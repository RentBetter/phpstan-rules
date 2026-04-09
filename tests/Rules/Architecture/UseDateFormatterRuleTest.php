<?php

declare(strict_types=1);

namespace RentBetter\PHPStanRules\Tests\Rules\Architecture;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use RentBetter\PHPStanRules\Rules\Architecture\UseDateFormatterRule;

/** @extends RuleTestCase<UseDateFormatterRule> */
final class UseDateFormatterRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new UseDateFormatterRule();
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/data/use-date-formatter.php'], [
            ['Use DateFormatter::formatDatetime() or DateFormatter::formatDate() instead of ->format().', 40],
            ['Use DateFormatter::formatDatetime() or DateFormatter::formatDate() instead of ->format().', 41],
        ]);
    }
}
