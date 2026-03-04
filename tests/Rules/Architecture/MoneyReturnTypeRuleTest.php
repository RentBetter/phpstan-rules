<?php

declare(strict_types=1);

namespace RentBetter\PHPStanRules\Tests\Rules\Architecture;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use RentBetter\PHPStanRules\Rules\Architecture\MoneyReturnTypeRule;

/** @extends RuleTestCase<MoneyReturnTypeRule> */
final class MoneyReturnTypeRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new MoneyReturnTypeRule();
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/data/money-return-type.php'], [
            ['Public methods should return MoneyInterface, not MoneyModelV2.', 10],
            ['Public methods should return ?MoneyInterface, not ?MoneyModelV2.', 15],
        ]);
    }
}
