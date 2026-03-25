<?php

declare(strict_types=1);

namespace RentBetter\PHPStanRules\Rules\Doctrine;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use RentBetter\PHPStanRules\Rules\LevelAwareRule;

/**
 * Entities with #[ORM\Entity] must also have #[ORM\ChangeTrackingPolicy('DEFERRED_EXPLICIT')].
 *
 * Deferred explicit means Doctrine only tracks changes on entities you explicitly persist(),
 * preventing accidental flushes of unintended changes. It also improves performance by
 * skipping dirty-checking on unpersisted entities and gives control over batch saving
 * and flush timing.
 *
 * @implements Rule<Class_>
 */
final class EntityDeferredExplicitRule implements Rule
{
    use LevelAwareRule;

    private const int MIN_LEVEL = 6;

    public function __construct(
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

        if (!$this->hasAttribute($node, 'Entity')) {
            return [];
        }

        if ($this->hasDeferredExplicit($node)) {
            return [];
        }

        return [
            RuleErrorBuilder::message(\sprintf(
                'Entity %s is missing #[ORM\\ChangeTrackingPolicy(\'DEFERRED_EXPLICIT\')]. All entities must use deferred explicit change tracking.',
                $node->name?->name ?? '(anonymous)',
            ))
                ->identifier('rentbetter.entityDeferredExplicit')
                ->build(),
        ];
    }

    private function hasAttribute(Class_ $node, string $shortName): bool
    {
        foreach ($node->attrGroups as $attrGroup) {
            foreach ($attrGroup->attrs as $attr) {
                $name = $attr->name->toString();
                if ($shortName === $name
                    || "Doctrine\\ORM\\Mapping\\{$shortName}" === $name
                    || "ORM\\{$shortName}" === $name
                ) {
                    return true;
                }
            }
        }

        return false;
    }

    private function hasDeferredExplicit(Class_ $node): bool
    {
        foreach ($node->attrGroups as $attrGroup) {
            foreach ($attrGroup->attrs as $attr) {
                $name = $attr->name->toString();
                if ('ChangeTrackingPolicy' !== $name
                    && 'Doctrine\ORM\Mapping\ChangeTrackingPolicy' !== $name
                    && 'ORM\ChangeTrackingPolicy' !== $name
                ) {
                    continue;
                }

                // Check first positional arg or named 'value' arg
                foreach ($attr->args as $arg) {
                    if ($arg->value instanceof Node\Scalar\String_
                        && 'DEFERRED_EXPLICIT' === $arg->value->value
                    ) {
                        return true;
                    }
                }
            }
        }

        return false;
    }
}
