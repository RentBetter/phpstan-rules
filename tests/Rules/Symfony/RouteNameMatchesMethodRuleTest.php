<?php

declare(strict_types=1);

namespace RentBetter\PHPStanRules\Tests\Rules\Symfony;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use RentBetter\PHPStanRules\Rules\Symfony\RouteNameMatchesMethodRule;

/**
 * @extends RuleTestCase<RouteNameMatchesMethodRule>
 */
final class RouteNameMatchesMethodRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new RouteNameMatchesMethodRule();
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/data/route-name-matches-method.php'], [
            [
                "Route name 'getAllThings' does not match method name. Expected 'listThings' (from listThingsAction()).",
                9,
            ],
        ]);
    }
}
