<?php

declare(strict_types=1);

namespace PTGS\PHPStanRules\Tests\Rules\Architecture;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PTGS\PHPStanRules\Rules\Architecture\NamedArgumentOrderRule;

/**
 * @extends RuleTestCase<NamedArgumentOrderRule>
 */
final class NamedArgumentOrderRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new NamedArgumentOrderRule($this->createReflectionProvider());
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/data/named-argument-order.php'], [
            [
                'Named argument $a appears after $b but is declared before it; reorder named arguments to match the parameter definition.',
                27,
            ],
            [
                'Named argument $b appears after $c but is declared before it; reorder named arguments to match the parameter definition.',
                28,
            ],
            [
                'Named argument $a appears after $b but is declared before it; reorder named arguments to match the parameter definition.',
                28,
            ],
            [
                'Named argument $b appears after $c but is declared before it; reorder named arguments to match the parameter definition.',
                29,
            ],
            [
                'Named argument $name appears after $id but is declared before it; reorder named arguments to match the parameter definition.',
                31,
            ],
            [
                'Named argument $name appears after $id but is declared before it; reorder named arguments to match the parameter definition.',
                34,
            ],
        ]);
    }
}
