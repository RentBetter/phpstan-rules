<?php

declare(strict_types=1);

namespace PTGS\PHPStanRules\Rules\Architecture;

use PhpParser\Node;
use PhpParser\Node\Expr\CallLike;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\NullsafeMethodCall;
use PhpParser\Node\Expr\StaticCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ParametersAcceptor;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PTGS\PHPStanRules\Rules\LevelAwareRule;

/**
 * Named arguments must appear in the same order as the parameter definition.
 *
 * Example:
 *   function foo(int $a, int $b, int $c) {}
 *   foo(a: 1, c: 3);       // OK
 *   foo(b: 2, a: 1);       // ERROR — $a is declared before $b
 *
 * Skips calls where the target callable cannot be resolved through reflection
 * (dynamic method names, unknown classes, closures from variables, etc.).
 *
 * @implements Rule<CallLike>
 */
final class NamedArgumentOrderRule implements Rule
{
    use LevelAwareRule;

    private const int MIN_LEVEL = 5;

    public function __construct(
        private readonly ReflectionProvider $reflectionProvider,
        private readonly ?int $ruleLevel = null,
    ) {}

    public function getNodeType(): string
    {
        return CallLike::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if ($this->belowMinLevel()) {
            return [];
        }

        if (!$node instanceof CallLike) {
            return [];
        }

        $acceptor = $this->resolveAcceptor($node, $scope);
        if (null === $acceptor) {
            return [];
        }

        $indexByName = [];
        foreach ($acceptor->getParameters() as $i => $parameter) {
            $indexByName[$parameter->getName()] = $i;
        }

        $errors = [];
        $previousIndex = -1;
        $previousName = null;

        foreach ($node->getArgs() as $arg) {
            if (null === $arg->name) {
                continue;
            }

            $name = $arg->name->toString();
            if (!isset($indexByName[$name])) {
                continue;
            }

            $currentIndex = $indexByName[$name];
            if ($currentIndex < $previousIndex) {
                $errors[] = RuleErrorBuilder::message(\sprintf(
                    'Named argument $%s appears after $%s but is declared before it; reorder named arguments to match the parameter definition.',
                    $name,
                    $previousName,
                ))
                    ->identifier('ptgs.namedArgumentOrder')
                    ->line($arg->getStartLine())
                    ->build();
            }

            $previousIndex = $currentIndex;
            $previousName = $name;
        }

        return $errors;
    }

    private function resolveAcceptor(CallLike $node, Scope $scope): ?ParametersAcceptor
    {
        $args = $node->getArgs();

        if ($node instanceof MethodCall || $node instanceof NullsafeMethodCall) {
            if (!$node->name instanceof Node\Identifier) {
                return null;
            }

            $calleeType = $scope->getType($node->var);
            $methodName = $node->name->toString();
            if (!$calleeType->hasMethod($methodName)->yes()) {
                return null;
            }

            $method = $calleeType->getMethod($methodName, $scope);

            return ParametersAcceptorSelector::selectFromArgs($scope, $args, $method->getVariants());
        }

        if ($node instanceof StaticCall) {
            if (!$node->name instanceof Node\Identifier || !$node->class instanceof Node\Name) {
                return null;
            }

            $className = $scope->resolveName($node->class);
            if (!$this->reflectionProvider->hasClass($className)) {
                return null;
            }

            $classReflection = $this->reflectionProvider->getClass($className);
            $methodName = $node->name->toString();
            if (!$classReflection->hasMethod($methodName)) {
                return null;
            }

            $method = $classReflection->getMethod($methodName, $scope);

            return ParametersAcceptorSelector::selectFromArgs($scope, $args, $method->getVariants());
        }

        if ($node instanceof FuncCall) {
            if (!$node->name instanceof Node\Name) {
                return null;
            }

            if (!$this->reflectionProvider->hasFunction($node->name, $scope)) {
                return null;
            }

            $function = $this->reflectionProvider->getFunction($node->name, $scope);

            return ParametersAcceptorSelector::selectFromArgs($scope, $args, $function->getVariants());
        }

        if ($node instanceof New_) {
            if (!$node->class instanceof Node\Name) {
                return null;
            }

            $className = $scope->resolveName($node->class);
            if (!$this->reflectionProvider->hasClass($className)) {
                return null;
            }

            $classReflection = $this->reflectionProvider->getClass($className);
            if (!$classReflection->hasConstructor()) {
                return null;
            }

            $constructor = $classReflection->getConstructor();

            return ParametersAcceptorSelector::selectFromArgs($scope, $args, $constructor->getVariants());
        }

        return null;
    }
}
