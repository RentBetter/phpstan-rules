<?php

declare(strict_types=1);

namespace RentBetter\PHPStanRules\Tests\Rules\Doctrine;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use RentBetter\PHPStanRules\Rules\Doctrine\NoPublicCollectionReturnRule;

/**
 * @extends RuleTestCase<NoPublicCollectionReturnRule>
 */
final class NoPublicCollectionReturnRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new NoPublicCollectionReturnRule();
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/data/collection-return.php'], [
            [
                'Method App\Entity\User::getItems() should not return a Doctrine Collection. Return an array instead.',
                19,
            ],
        ]);
    }
}
