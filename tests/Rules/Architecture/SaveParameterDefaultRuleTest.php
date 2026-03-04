<?php

declare(strict_types=1);

namespace RentBetter\PHPStanRules\Tests\Rules\Architecture;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use RentBetter\PHPStanRules\Rules\Architecture\SaveParameterDefaultRule;

/** @extends RuleTestCase<SaveParameterDefaultRule> */
final class SaveParameterDefaultRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new SaveParameterDefaultRule();
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/data/save-parameter-default.php'], [
            ['Public method $save parameter should default to true, not false.', 7],
            ['Public method $flush parameter should default to true, not false.', 11],
        ]);
    }
}
