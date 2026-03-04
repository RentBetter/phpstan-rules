<?php

declare(strict_types=1);

namespace RentBetter\PHPStanRules\Tests\Rules\Symfony;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use RentBetter\PHPStanRules\Rules\Symfony\RouteIdParamMustBeStringRule;

/** @extends RuleTestCase<RouteIdParamMustBeStringRule> */
final class RouteIdParamMustBeStringRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new RouteIdParamMustBeStringRule();
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/data/route-id-param-string.php'], [
            ['Route ID parameter $thingId must be typed as string, got int.', 9],
            ['Route ID parameter $thingId must be typed as string, got App\Entity\Thing.', 14],
            ['Route ID parameter $itemId must be typed as string, got int.', 29],
        ]);
    }
}
