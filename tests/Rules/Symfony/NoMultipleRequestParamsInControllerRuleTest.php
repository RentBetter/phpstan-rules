<?php

declare(strict_types=1);

namespace RentBetter\PHPStanRules\Tests\Rules\Symfony;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use RentBetter\PHPStanRules\Rules\Symfony\NoMultipleRequestParamsInControllerRule;

/** @extends RuleTestCase<NoMultipleRequestParamsInControllerRule> */
final class NoMultipleRequestParamsInControllerRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new NoMultipleRequestParamsInControllerRule();
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/data/multiple-request-params.php'], [
            ['Controller method accesses 2 request parameters directly (page, sort). Use a form type or DTO instead.', 11],
            ['Controller method accesses 3 request parameters directly (page, sort, active). Use a form type or DTO instead.', 18],
            ['Controller method accesses 2 request parameters directly (page, active). Use a form type or DTO instead.', 26],
        ]);
    }
}
