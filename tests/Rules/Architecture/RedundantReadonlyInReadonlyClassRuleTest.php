<?php

declare(strict_types=1);

namespace PTGS\PHPStanRules\Tests\Rules\Architecture;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PTGS\PHPStanRules\Rules\Architecture\RedundantReadonlyInReadonlyClassRule;

/**
 * @extends RuleTestCase<RedundantReadonlyInReadonlyClassRule>
 */
final class RedundantReadonlyInReadonlyClassRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new RedundantReadonlyInReadonlyClassRule();
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/data/redundant-readonly-in-readonly-class.php'], [
            [
                'Property $name must not be declared readonly inside readonly class App\Service\HasReadonlyProperty.',
                7,
            ],
            [
                'Property $slug must not be declared readonly inside readonly class App\Service\HasMultipleReadonlyProperties.',
                17,
            ],
            [
                'Property $status must not be declared readonly inside readonly class App\Service\HasMultipleReadonlyProperties.',
                17,
            ],
            [
                'Promoted property $title must not be declared readonly inside readonly class App\Service\HasReadonlyPromotedProperty.',
                22,
            ],
            [
                'Promoted property $id must not be declared readonly inside readonly class App\Service\HasMixedPromotedProperties.',
                32,
            ],
        ]);
    }
}
