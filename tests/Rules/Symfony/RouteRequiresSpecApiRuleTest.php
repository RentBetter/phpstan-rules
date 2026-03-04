<?php

declare(strict_types=1);

namespace RentBetter\PHPStanRules\Tests\Rules\Symfony;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use RentBetter\PHPStanRules\Rules\Symfony\RouteRequiresSpecApiRule;

/** @extends RuleTestCase<RouteRequiresSpecApiRule> */
final class RouteRequiresSpecApiRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new RouteRequiresSpecApiRule();
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/data/route-requires-spec-api.php'], [
            ['Route method is missing #[Spec\Api] attribute for API documentation.', 9],
        ]);
    }
}
