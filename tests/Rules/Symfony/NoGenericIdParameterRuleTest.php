<?php

declare(strict_types=1);

namespace RentBetter\PHPStanRules\Tests\Rules\Symfony;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use RentBetter\PHPStanRules\Rules\Symfony\NoGenericIdParameterRule;

/**
 * @extends RuleTestCase<NoGenericIdParameterRule>
 */
final class NoGenericIdParameterRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new NoGenericIdParameterRule();
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/data/generic-id.php'], [
            [
                'Route parameter should use a descriptive name like $tenancyId instead of $id.',
                10,
            ],
        ]);
    }
}
