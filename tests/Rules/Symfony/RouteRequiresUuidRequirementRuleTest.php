<?php

declare(strict_types=1);

namespace RentBetter\PHPStanRules\Tests\Rules\Symfony;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use RentBetter\PHPStanRules\Rules\Symfony\RouteRequiresUuidRequirementRule;

/**
 * @extends RuleTestCase<RouteRequiresUuidRequirementRule>
 */
final class RouteRequiresUuidRequirementRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new RouteRequiresUuidRequirementRule();
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/data/route-uuid-requirement.php'], [
            [
                'Route parameter {tenancyId} should have a requirements: constraint (e.g. Uuid::REGEX).',
                9,
            ],
        ]);
    }
}
