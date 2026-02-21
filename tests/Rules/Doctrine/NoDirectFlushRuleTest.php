<?php

declare(strict_types=1);

namespace RentBetter\PHPStanRules\Tests\Rules\Doctrine;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use RentBetter\PHPStanRules\Rules\Doctrine\NoDirectFlushRule;

/**
 * @extends RuleTestCase<NoDirectFlushRule>
 */
final class NoDirectFlushRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new NoDirectFlushRule();
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/data/direct-flush.php'], [
            [
                'Avoid calling EntityManagerInterface::flush() directly. Use a service with a $save parameter instead.',
                15,
            ],
        ]);
    }
}
