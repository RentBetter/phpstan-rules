<?php

declare(strict_types=1);

namespace PTGS\PHPStanRules\Tests\Rules\Serialization;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PTGS\PHPStanRules\Rules\Serialization\NoNullInJsonSerializeRule;

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
                "jsonSerialize() returns a raw array where 'name' may be null. Wrap it in array_filter_nulls() to strip null values.",
                11,
            ],
            [
                "jsonSerialize() returns a raw array where 'deletedAt' may be null. Wrap it in array_filter_nulls() to strip null values.",
                62,
            ],
            [
                "jsonSerialize() returns a raw array where 'deletedAt' may be null. Wrap it in array_filter_nulls() to strip null values.",
                100,
            ],
            [
                'jsonSerialize() returns a raw array where a spread value may be null. Wrap it in array_filter_nulls() to strip null values.',
                118,
            ],
            [
                "jsonSerialize() returns a raw array where 'label' may be null. Wrap it in array_filter_nulls() to strip null values.",
                224,
            ],
            [
                'jsonSerialize() returns a raw array where [0] may be null. Wrap it in array_filter_nulls() to strip null values.',
                241,
            ],
        ]);
    }

    public function testRuleIsSilentBelowItsLevel(): void
    {
        $rule = new NoNullInJsonSerializeRule(ruleLevel: 7);
        self::assertSame([], $rule->processNode(
            new \PhpParser\Node\Stmt\Return_(new \PhpParser\Node\Expr\Array_()),
            $this->createMock(\PHPStan\Analyser\Scope::class),
        ));
    }
}
