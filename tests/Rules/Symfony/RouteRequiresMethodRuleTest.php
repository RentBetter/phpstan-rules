<?php

declare(strict_types=1);

namespace RentBetter\PHPStanRules\Tests\Rules\Symfony;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use RentBetter\PHPStanRules\Rules\Symfony\RouteRequiresMethodRule;

/**
 * @extends RuleTestCase<RouteRequiresMethodRule>
 */
final class RouteRequiresMethodRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new RouteRequiresMethodRule();
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/data/route-requires-method.php'], [
            [
                "Route on listThingsAction() is missing the methods: parameter. Specify methods: 'GET' (or POST, PUT, DELETE).",
                9,
            ],
        ]);
    }
}
