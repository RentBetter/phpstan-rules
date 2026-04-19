<?php

declare(strict_types=1);

namespace PTGS\PHPStanRules\Tests\Rules\Symfony;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PTGS\PHPStanRules\Rules\Symfony\ActionMethodNamingRule;

/**
 * @extends RuleTestCase<ActionMethodNamingRule>
 */
final class ActionMethodNamingRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new ActionMethodNamingRule();
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/data/action-method-naming.php'], [
            [
                'Route method listThings() should be named listThingsAction().',
                9,
            ],
        ]);
    }
}
