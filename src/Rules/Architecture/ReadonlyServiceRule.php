<?php

declare(strict_types=1);

namespace RentBetter\PHPStanRules\Rules\Architecture;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Service classes should be declared readonly — but only when it's actually possible:
 * - Must not extend a non-readonly class (PHP doesn't allow it)
 * - All properties (including promoted params) must already be readonly
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

        if ($node->isAbstract() || $node->isReadonly()) {
            return [];
        }

        $fqcn = $node->namespacedName?->toString() ?? $node->name->name;

        if (!$this->isInNamespace($fqcn)) {
            return [];
        }

        if ($this->isExcluded($fqcn)) {
            return [];
        }

        // If extends another class, check if parent is readonly.
        // If we can't resolve the parent, skip (safe default — don't flag what we can't verify).
        if (null !== $node->extends) {
            $classReflection = $scope->getClassReflection();
            if (!$classReflection instanceof ClassReflection) {
                return [];
            }
            $parent = $classReflection->getParentClass();
            if (!$parent instanceof ClassReflection || !$parent->isReadOnly()) {
                return [];
            }
        }

        // All properties must already be readonly (or no properties at all)
        if (!$this->allPropertiesReadonly($node)) {
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

    private function allPropertiesReadonly(Class_ $node): bool
    {
        $hasProperties = false;

        // Check declared properties
        foreach ($node->stmts as $stmt) {
            if ($stmt instanceof Property) {
                $hasProperties = true;
                if (!$stmt->isReadonly()) {
                    return false;
                }
            }
        }

        // Check promoted constructor parameters
        $constructor = $node->getMethod('__construct');
        if (null !== $constructor) {
            foreach ($constructor->params as $param) {
                if (0 !== ($param->flags & Class_::MODIFIER_PUBLIC)
                    || 0 !== ($param->flags & Class_::MODIFIER_PROTECTED)
                    || 0 !== ($param->flags & Class_::MODIFIER_PRIVATE)
                ) {
                    // This is a promoted parameter (it has a visibility modifier)
                    $hasProperties = true;
                    if (0 === ($param->flags & Class_::MODIFIER_READONLY)) {
                        return false;
                    }
                }
            }
        }

        // No properties at all — constructor with only non-promoted params, or no constructor.
        // A class with no properties can always be readonly, flag it.
        return $hasProperties;
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
