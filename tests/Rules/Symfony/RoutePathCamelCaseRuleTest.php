<?php

declare(strict_types=1);

namespace PTGS\PHPStanRules\Tests\Rules\Symfony;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PTGS\PHPStanRules\Rules\Symfony\RoutePathCamelCaseRule;

/** @extends RuleTestCase<RoutePathCamelCaseRule> */
final class RoutePathCamelCaseRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new RoutePathCamelCaseRule();
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/data/route-path-camel-case.php'], [
            ['Route path segment "payment_accounts" uses snake_case. Use camelCase instead.', 9],
            ['Route path segment "some_thing" uses snake_case. Use camelCase instead.', 14],
        ]);
    }
}
