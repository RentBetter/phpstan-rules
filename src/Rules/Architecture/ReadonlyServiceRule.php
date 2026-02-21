<?php

declare(strict_types=1);

namespace RentBetter\PHPStanRules\Rules\Architecture;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Service classes should be declared readonly.
 * Configurable namespace includes and exclude patterns.
 *
 * @implements Rule<Class_>
 */
final class ReadonlyServiceRule implements Rule
{
    /**
     * @param list<string> $namespaceIncludes
     * @param list<string> $excludePatterns
     */
    public function __construct(
        private readonly array $namespaceIncludes = ['App\\'],
        private readonly array $excludePatterns = ['Controller', 'Command', 'Entity', 'Migration'],
    ) {}

    public function getNodeType(): string
    {
        return Class_::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if (null === $node->name) {
            return [];
        }

        // Skip abstract classes, enums (handled separately), interfaces, traits
        if ($node->isAbstract()) {
            return [];
        }

        $fqcn = $node->namespacedName?->toString() ?? $node->name->name;

        if (!$this->isInNamespace($fqcn)) {
            return [];
        }

        if ($this->isExcluded($fqcn)) {
            return [];
        }

        if ($node->isReadonly()) {
            return [];
        }

        return [
            RuleErrorBuilder::message(\sprintf(
                'Service class %s should be declared readonly.',
                $fqcn,
            ))
                ->identifier('rentbetter.readonlyService')
                ->build(),
        ];
    }

    private function isInNamespace(string $fqcn): bool
    {
        foreach ($this->namespaceIncludes as $prefix) {
            if (str_starts_with($fqcn, $prefix)) {
                return true;
            }
        }

        return false;
    }

    private function isExcluded(string $fqcn): bool
    {
        foreach ($this->excludePatterns as $pattern) {
            if (str_contains($fqcn, $pattern)) {
                return true;
            }
        }

        return false;
    }
}
