<?php

declare(strict_types=1);

namespace RentBetter\PHPStanRules\Tests\Rules\Serialization;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use RentBetter\PHPStanRules\Rules\Serialization\NoNullInJsonSerializeRule;

/**
 * @extends RuleTestCase<NoNullInJsonSerializeRule>
 */
final class NoNullInJsonSerializeRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new NoNullInJsonSerializeRule();
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/data/null-in-json-serialize.php'], [
            [
                'jsonSerialize() returns a raw array. Wrap it in array_filter_nulls() to strip null values.',
                11,
            ],
        ]);
    }
}
