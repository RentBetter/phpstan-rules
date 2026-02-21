<?php

declare(strict_types=1);

namespace RentBetter\PHPStanRules\Tests\Rules\Doctrine;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use RentBetter\PHPStanRules\Rules\Doctrine\NoRepositoryInControllerRule;

/**
 * @extends RuleTestCase<NoRepositoryInControllerRule>
 */
final class NoRepositoryInControllerRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new NoRepositoryInControllerRule();
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/data/repository-in-controller.php'], [
            [
                'Controller App\Controller\BadRepoController should not inject App\Repository\UserRepository. Use a service instead.',
                11,
            ],
        ]);
    }
}
