<?php

declare(strict_types=1);

namespace RentBetter\PHPStanRules\Tests\Rules\Enum;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use RentBetter\PHPStanRules\Rules\Enum\StatusColumnMustBeEnumRule;

/**
 * @extends RuleTestCase<StatusColumnMustBeEnumRule>
 */
final class StatusColumnMustBeEnumRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new StatusColumnMustBeEnumRule();
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/data/status-column.php'], [
            [
                'Status property $status has a #[Column] without enumType:. Use #[Column(enumType: MyStatusEnum::class)].',
                9,
            ],
            [
                'Status property $paymentStatus has a #[Column] without enumType:. Use #[Column(enumType: MyStatusEnum::class)].',
                12,
            ],
        ]);
    }
}
