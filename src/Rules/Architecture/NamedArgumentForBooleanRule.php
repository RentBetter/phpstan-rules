<?php

declare(strict_types=1);

namespace RentBetter\PHPStanRules\Rules\Architecture;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\ObjectType;

/**
 * Boolean literals (true/false) should be passed as named arguments
 * to project methods for readability.
 *
 * Skips: PHP built-in functions, vendor code, and already-named arguments.
 *
 * @implements Rule<Node>
 */
final class NamedArgumentForBooleanRule implements Rule
{
    /**
     * @param list<string> $projectNamespaces
     */
    public function __construct(
        private readonly array $projectNamespaces = ['App\\'],
    ) {}

    public function getNodeType(): string
    {
        return Node::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if (!$node instanceof MethodCall && !$node instanceof StaticCall) {
            return [];
        }

        if (!$node->name instanceof Node\Identifier) {
            return [];
        }

        $errors = [];
        foreach ($node->getArgs() as $i => $arg) {
            // Already a named argument — skip
            if (null !== $arg->name) {
                continue;
            }

            // Check if it's a boolean literal
            if (!$arg->value instanceof Node\Expr\ConstFetch) {
                continue;
            }

            $constName = strtolower($arg->value->name->toString());
            if ('true' !== $constName && 'false' !== $constName) {
                continue;
            }

            // Check if the called method is in a project namespace
            if (!$this->isProjectMethod($node, $scope)) {
                continue;
            }

            $errors[] = RuleErrorBuilder::message(\sprintf(
                'Boolean literal %s should be passed as a named argument for readability.',
                $constName,
            ))
                ->identifier('rentbetter.namedArgumentForBoolean')
                ->line($arg->getStartLine())
                ->build();
        }

        return $errors;
    }

    private function isProjectMethod(MethodCall|StaticCall $node, Scope $scope): bool
    {
        if ($node instanceof MethodCall) {
            $callerType = $scope->getType($node->var);
            $classNames = $callerType->getObjectClassNames();
        } else {
            if ($node->class instanceof Node\Name) {
                $classNames = [$node->class->toString()];
                // Resolve 'self', 'static', 'parent'
                if (\in_array($classNames[0], ['self', 'static', 'parent'], true)) {
                    $classReflection = $scope->getClassReflection();
                    if (null === $classReflection) {
                        return false;
                    }
                    $classNames = [$classReflection->getName()];
                }
            } else {
                return false;
            }
        }

        foreach ($classNames as $className) {
            foreach ($this->projectNamespaces as $prefix) {
                if (str_starts_with($className, $prefix)) {
                    return true;
                }
            }
        }

        return false;
    }
}
