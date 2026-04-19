<?php

declare(strict_types=1);

namespace PTGS\PHPStanRules\Rules\Architecture;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PTGS\PHPStanRules\Rules\LevelAwareRule;
use PTGS\PHPStanRules\Rules\NamespaceGroupResolver;

/**
 * Enforces dependency boundaries between namespace groups.
 *
 * Configured as a map of from-group → list of {group, reason}. For every class
 * matching the from-group, every constructor parameter is checked against each
 * forbidden group. A match yields an error annotated with the configured reason.
 *
 * Matching is by FQCN string (subtype detection is not supported — list the
 * interface or each concrete class explicitly in the group definition).
 *
 * @implements Rule<Class_>
 */
final class ForbiddenDependencyRule implements Rule
{
    use LevelAwareRule;

    private const int MIN_LEVEL = 5;

    /**
     * @param array<string, list<array{group: string, reason: string}>> $forbiddenDependencies
     */
    public function __construct(
        private readonly NamespaceGroupResolver $resolver,
        private readonly array $forbiddenDependencies,
        private readonly ?int $ruleLevel = null,
    ) {}

    public function getNodeType(): string
    {
        return Class_::class;
    }

    /** @return list<IdentifierRuleError> */
    public function processNode(Node $node, Scope $scope): array
    {
        if ($this->belowMinLevel()) {
            return [];
        }

        if (null === $node->name) {
            return [];
        }

        $constructor = $node->getMethod('__construct');
        if (null === $constructor) {
            return [];
        }

        $fqcn = $node->namespacedName?->toString() ?? $node->name->name;
        $fromGroups = $this->matchingFromGroups($fqcn);
        if ([] === $fromGroups) {
            return [];
        }

        $errors = [];
        foreach ($constructor->params as $param) {
            $paramTypeFqcn = $this->getParamTypeFqcn($param, $scope);
            if (null === $paramTypeFqcn) {
                continue;
            }

            foreach ($fromGroups as $fromGroup) {
                foreach ($this->forbiddenDependencies[$fromGroup] as $forbidden) {
                    if (!$this->resolver->inGroup($paramTypeFqcn, $forbidden['group'])) {
                        continue;
                    }

                    $errors[] = RuleErrorBuilder::message(\sprintf(
                        '%s %s may NOT depend on %s (group: %s). %s',
                        ucfirst($fromGroup),
                        $fqcn,
                        $paramTypeFqcn,
                        $forbidden['group'],
                        $forbidden['reason'],
                    ))
                        ->identifier('ptgs.forbiddenDependency')
                        ->line($param->getStartLine())
                        ->build();
                }
            }
        }

        return $errors;
    }

    /**
     * @return list<string>
     */
    private function matchingFromGroups(string $fqcn): array
    {
        $matches = [];
        foreach (array_keys($this->forbiddenDependencies) as $fromGroup) {
            if ($this->resolver->inGroup($fqcn, $fromGroup)) {
                $matches[] = $fromGroup;
            }
        }

        return $matches;
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
}
