<?php

declare(strict_types=1);

namespace RentBetter\PHPStanRules\Tests\Rules\Doctrine;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use RentBetter\PHPStanRules\Rules\Doctrine\NoEntityManagerInControllerRule;

/**
 * @extends RuleTestCase<NoEntityManagerInControllerRule>
 */
final class NoEntityManagerInControllerRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new NoEntityManagerInControllerRule();
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/data/entity-manager-in-controller.php'], [
            [
                'Controller App\Controller\BadController should not inject EntityManagerInterface. Use a service instead.',
                10,
            ],
        ]);
    }
}
