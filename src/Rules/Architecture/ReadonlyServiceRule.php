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
use RentBetter\PHPStanRules\Rules\LevelAwareRule;
use RentBetter\PHPStanRules\Rules\NamespaceGroupResolver;

/**
 * Service classes should be declared readonly — but only when it's actually possible:
 * - Must not extend a non-readonly class (PHP doesn't allow it)
 * - All properties (including promoted params) must already be readonly
 *
 * @implements Rule<Class_>
 */
final class ReadonlyServiceRule implements Rule
{
    use LevelAwareRule;

    private const int MIN_LEVEL = 8;

    public function __construct(
        private readonly NamespaceGroupResolver $resolver,
        private readonly ?int $ruleLevel = null,
    ) {}

    public function getNodeType(): string
    {
        return Class_::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if ($this->belowMinLevel()) {
            return [];
        }

        if (null === $node->name) {
            return [];
        }

        if ($node->isAbstract() || $node->isReadonly()) {
            return [];
        }

        $fqcn = $node->namespacedName?->toString() ?? $node->name->name;

        if (!$this->resolver->inGroup($fqcn, 'service')) {
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

        foreach ($node->stmts as $stmt) {
            if ($stmt instanceof Property) {
                $hasProperties = true;
                if (!$stmt->isReadonly()) {
                    return false;
                }
            }
        }

        $constructor = $node->getMethod('__construct');
        if (null !== $constructor) {
            foreach ($constructor->params as $param) {
                if (0 !== ($param->flags & Class_::MODIFIER_PUBLIC)
                    || 0 !== ($param->flags & Class_::MODIFIER_PROTECTED)
                    || 0 !== ($param->flags & Class_::MODIFIER_PRIVATE)
                ) {
                    $hasProperties = true;
                    if (0 === ($param->flags & Class_::MODIFIER_READONLY)) {
                        return false;
                    }
                }
            }
        }

        return $hasProperties;
    }
}
