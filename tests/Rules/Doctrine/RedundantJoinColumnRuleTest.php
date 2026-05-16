<?php

declare(strict_types=1);

namespace PTGS\PHPStanRules\Tests\Rules\Doctrine;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PTGS\PHPStanRules\Rules\Doctrine\RedundantJoinColumnRule;

/**
 * @extends RuleTestCase<RedundantJoinColumnRule>
 */
final class RedundantJoinColumnRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new RedundantJoinColumnRule();
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/data/redundant-join-column.php'], [
            [
                'Redundant JoinColumn on property $type — every value matches the Doctrine default; the attribute can be removed.',
                11,
            ],
            [
                'Redundant JoinColumn on property $accessLevel — every value matches the Doctrine default; the attribute can be removed.',
                15,
            ],
            [
                'Redundant JoinColumn argument name on property $adminRole — this matches the Doctrine default.',
                19,
            ],
            [
                'Redundant JoinColumn argument name on property $user2faToken — this matches the Doctrine default.',
                23,
            ],
            [
                'Redundant JoinColumn argument nullable on property $withRedundantNullable — this matches the Doctrine default.',
                27,
            ],
            [
                'Redundant JoinColumn argument unique on property $withRedundantUnique — this matches the Doctrine default.',
                31,
            ],
            [
                'Redundant JoinColumn argument options on property $withEmptyOptions — this matches the Doctrine default.',
                35,
            ],
            [
                'Redundant JoinColumn argument onDelete on property $withNullDefaults — this matches the Doctrine default.',
                39,
            ],
            [
                'Redundant JoinColumn argument columnDefinition on property $withNullDefaults — this matches the Doctrine default.',
                39,
            ],
            [
                'Redundant JoinColumn argument referencedColumnName on property $withRedundantReferencedColumn — this matches the Doctrine default.',
                43,
            ],
        ]);
    }
}
