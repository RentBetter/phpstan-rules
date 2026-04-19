<?php

declare(strict_types=1);

namespace PTGS\PHPStanRules\Tests\Rules\Doctrine;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PTGS\PHPStanRules\Rules\Doctrine\EntityDeferredExplicitRule;

/**
 * @extends RuleTestCase<EntityDeferredExplicitRule>
 */
final class EntityDeferredExplicitRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new EntityDeferredExplicitRule();
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/data/entity-deferred-explicit.php'], [
            [
                "Entity MissingPolicy is missing #[ORM\\ChangeTrackingPolicy('DEFERRED_EXPLICIT')]. All entities must use deferred explicit change tracking.",
                7,
            ],
            [
                "Entity WrongPolicy is missing #[ORM\\ChangeTrackingPolicy('DEFERRED_EXPLICIT')]. All entities must use deferred explicit change tracking.",
                26,
            ],
        ]);
    }
}
