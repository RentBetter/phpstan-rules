<?php

declare(strict_types=1);

namespace PTGS\PHPStanRules\Tests\Rules\Symfony;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PTGS\PHPStanRules\Rules\Symfony\NoGenericIdParameterRule;

/**
 * @extends RuleTestCase<NoGenericIdParameterRule>
 */
final class NoGenericIdParameterRuleTest extends RuleTestCase
{
    private const string PATH_ERROR = 'Route path parameter should use a descriptive name like {tenancyId} instead of {id}.';
    private const string PARAM_ERROR = 'Route parameter should use a descriptive name like $tenancyId instead of $id.';

    protected function getRule(): Rule
    {
        return new NoGenericIdParameterRule();
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/data/generic-id.php'], [
            [self::PATH_ERROR, 9],
            [self::PATH_ERROR, 15],
            [self::PATH_ERROR, 20],
            [self::PARAM_ERROR, 26],
        ]);
    }
}
