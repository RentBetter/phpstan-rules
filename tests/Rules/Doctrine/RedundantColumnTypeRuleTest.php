<?php

declare(strict_types=1);

namespace RentBetter\PHPStanRules\Tests\Rules\Doctrine;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use RentBetter\PHPStanRules\Rules\Doctrine\RedundantColumnTypeRule;

/**
 * @extends RuleTestCase<RedundantColumnTypeRule>
 */
final class RedundantColumnTypeRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new RedundantColumnTypeRule();
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/data/redundant-column-type.php'], [
            [
                'Redundant type: Types::STRING on string property $name — Doctrine infers this from the PHP type.',
                11,
            ],
            [
                'Redundant type: Types::INTEGER on int property $count — Doctrine infers this from the PHP type.',
                14,
            ],
            [
                'Redundant type: Types::BOOLEAN on bool property $active — Doctrine infers this from the PHP type.',
                17,
            ],
            [
                'Redundant type: Types::FLOAT on float property $rate — Doctrine infers this from the PHP type.',
                20,
            ],
            [
                'Redundant type: Types::STRING on string property $nickname — Doctrine infers this from the PHP type.',
                23,
            ],
            [
                "Redundant type: 'string' on string property \$literal — Doctrine infers this from the PHP type.",
                26,
            ],
        ]);
    }
}
