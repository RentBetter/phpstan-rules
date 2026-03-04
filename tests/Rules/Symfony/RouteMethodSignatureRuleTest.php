<?php

declare(strict_types=1);

namespace RentBetter\PHPStanRules\Tests\Rules\Symfony;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use RentBetter\PHPStanRules\Rules\Symfony\RouteMethodSignatureRule;

/** @extends RuleTestCase<RouteMethodSignatureRule> */
final class RouteMethodSignatureRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new RouteMethodSignatureRule();
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/data/route-method-signature.php'], [
            ['Route method must have ApiRequest as first parameter.', 13],
            ['Route method first parameter must be typed as ApiRequest.', 19],
            ['Route method must declare ApiResponse return type.', 25],
            ['Route method must return ApiResponse.', 30],
        ]);
    }
}
