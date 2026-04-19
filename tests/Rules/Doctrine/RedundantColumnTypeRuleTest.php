<?php

declare(strict_types=1);

namespace PTGS\PHPStanRules\Tests\Rules\Doctrine;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PTGS\PHPStanRules\Rules\Doctrine\RedundantColumnTypeRule;

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
                12,
            ],
            [
                'Redundant type: Types::INTEGER on int property $count — Doctrine infers this from the PHP type.',
                15,
            ],
            [
                'Redundant type: Types::BOOLEAN on bool property $active — Doctrine infers this from the PHP type.',
                18,
            ],
            [
                'Redundant type: Types::FLOAT on float property $rate — Doctrine infers this from the PHP type.',
                21,
            ],
            [
                'Redundant type: Types::STRING on string property $nickname — Doctrine infers this from the PHP type.',
                24,
            ],
            [
                "Redundant type: 'string' on string property \$literal — Doctrine infers this from the PHP type.",
                27,
            ],
            [
                'Redundant type: Types::JSON on array property $data — Doctrine infers this from the PHP type.',
                39,
            ],
            [
                'Redundant type: Types::DATETIME_IMMUTABLE on DateTimeImmutable property $createdAt — Doctrine infers this from the PHP type.',
                42,
            ],
            [
                'Redundant type: Types::DATETIME_IMMUTABLE on DateTimeImmutable property $deletedAt — Doctrine infers this from the PHP type.',
                45,
            ],
            [
                'Redundant type: Types::JSON on array property $metadata — Doctrine infers this from the PHP type.',
                48,
            ],
        ]);
    }
}
