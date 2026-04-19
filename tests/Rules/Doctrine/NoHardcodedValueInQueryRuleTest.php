<?php

declare(strict_types=1);

namespace PTGS\PHPStanRules\Tests\Rules\Doctrine;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PTGS\PHPStanRules\Rules\Doctrine\NoHardcodedValueInQueryRule;

/**
 * @extends RuleTestCase<NoHardcodedValueInQueryRule>
 */
final class NoHardcodedValueInQueryRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new NoHardcodedValueInQueryRule();
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/data/hardcoded-query-value.php'], [
            ['Hardcoded numeric value in DQL IN() clause. Use a bound parameter (:param) or $enum->value instead.', 18],
            ['Hardcoded numeric value in DQL comparison. Use a bound parameter (:param) or $enum->value instead.', 24],
            ['Hardcoded numeric value in DQL comparison. Use a bound parameter (:param) or $enum->value instead.', 30],
            ['Hardcoded numeric value in DQL comparison. Use a bound parameter (:param) or $enum->value instead.', 36],
            ['Hardcoded numeric value in DQL IN() clause. Use a bound parameter (:param) or $enum->value instead.', 42],
            ['Hardcoded numeric value in DQL IN() clause. Use a bound parameter (:param) or $enum->value instead.', 48],
            ['Hardcoded numeric value in DQL comparison. Use a bound parameter (:param) or $enum->value instead.', 54],
            ['Hardcoded numeric value in DQL IN() clause. Use a bound parameter (:param) or $enum->value instead.', 60],
            ['Hardcoded numeric value in DQL comparison. Use a bound parameter (:param) or $enum->value instead.', 66],
            // expr() ERROR cases
            ['Hardcoded value in query expression. Use a bound parameter (:param) or $enum->value instead.', 123],
            ['Hardcoded value in query expression. Use a bound parameter (:param) or $enum->value instead.', 129],
            ['Hardcoded value in query expression. Use a bound parameter (:param) or $enum->value instead.', 135],
            ['Hardcoded value in query expression. Use a bound parameter (:param) or $enum->value instead.', 141],
            ['Hardcoded value in query expression. Use a bound parameter (:param) or $enum->value instead.', 147],
            ['Hardcoded value in query expression. Use a bound parameter (:param) or $enum->value instead.', 153],
        ]);
    }
}
