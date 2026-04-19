<?php

declare(strict_types=1);

namespace PTGS\PHPStanRules\Tests\Rules\Architecture;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PTGS\PHPStanRules\Rules\Architecture\NamedArgumentForBooleanRule;
use PTGS\PHPStanRules\Tests\Rules\TestGroups;

/**
 * @extends RuleTestCase<NamedArgumentForBooleanRule>
 */
final class NamedArgumentForBooleanRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new NamedArgumentForBooleanRule(TestGroups::defaultResolver());
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/data/named-argument-boolean.php'], [
            [
                'Boolean literal true should be passed as a named argument for readability.',
                9,
            ],
        ]);
    }
}
