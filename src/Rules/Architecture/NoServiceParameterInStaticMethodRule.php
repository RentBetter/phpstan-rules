<?php

declare(strict_types=1);

namespace PTGS\PHPStanRules\Rules\Architecture;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PTGS\PHPStanRules\Rules\LevelAwareRule;
use PTGS\PHPStanRules\Rules\NamespaceGroupResolver;

/**
 * A static method may not take a service, repository or DB handle as a parameter.
 *
 * Constructor injection is the only place a dependency should enter a class. A static
 * method that accepts one is injection by the back door: the caller has to know which
 * collaborators the method needs and reach for them itself, the dependency never appears
 * in the container graph, and the work the method does moves out of the layer that owns
 * it. It shows up most often on DTO factories — `Params::fromRequest($request, $service)`
 * — where the service is invariably resolving an entity, which belongs in a form where
 * a failure becomes a validation error rather than an exception.
 *
 * Take the collaborator in the constructor and make the method an instance method, or
 * leave the static factory to map plain request values and let the caller — which
 * already has the service injected — do the resolving.
 *
 * @implements Rule<ClassMethod>
 */
final class NoServiceParameterInStaticMethodRule implements Rule
{
    use LevelAwareRule;

    private const int MIN_LEVEL = 5;

    /**
     * Groups whose types are dependencies, not data.
     */
    private const array INJECTED_GROUPS = ['service', 'repository', 'dbAccess'];

    public function __construct(
        private readonly NamespaceGroupResolver $resolver,
        private readonly ?int $ruleLevel = null,
    ) {}

    public function getNodeType(): string
    {
        return ClassMethod::class;
    }

    /** @return list<IdentifierRuleError> */
    public function processNode(Node $node, Scope $scope): array
    {
        if ($this->belowMinLevel()) {
            return [];
        }

        if (!$node->isStatic() || [] === $node->params) {
            return [];
        }

        $errors = [];

        foreach ($node->params as $param) {
            if (null === $paramTypeFqcn = $this->getParamTypeFqcn($param, $scope)) {
                continue;
            }

            if (null === $group = $this->injectedGroup($paramTypeFqcn)) {
                continue;
            }

            $errors[] = RuleErrorBuilder::message(\sprintf(
                'Static method %s() takes %s (group: %s) as a parameter. Dependencies enter a class through its constructor — make this an instance method, or let the caller that already has %s injected do the work.',
                $node->name->name,
                $paramTypeFqcn,
                $group,
                $this->shortName($paramTypeFqcn),
            ))
                ->identifier('ptgs.noServiceParameterInStaticMethod')
                ->line($param->getStartLine())
                ->build();
        }

        return $errors;
    }

    private function injectedGroup(string $fqcn): ?string
    {
        foreach (self::INJECTED_GROUPS as $group) {
            if ($this->resolver->inGroup($fqcn, $group)) {
                return $group;
            }
        }

        return null;
    }

    private function getParamTypeFqcn(Node\Param $param, Scope $scope): ?string
    {
        $type = $param->type;
        if ($type instanceof Node\NullableType) {
            $type = $type->type;
        }
        if (!$type instanceof Node\Name) {
            return null;
        }

        return $scope->resolveName($type);
    }

    private function shortName(string $fqcn): string
    {
        $position = strrpos($fqcn, '\\');

        return false === $position ? $fqcn : substr($fqcn, $position + 1);
    }
}
