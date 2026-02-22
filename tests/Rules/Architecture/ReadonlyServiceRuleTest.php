<?php

declare(strict_types=1);

namespace RentBetter\PHPStanRules\Tests\Rules\Architecture;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use RentBetter\PHPStanRules\Rules\Architecture\ReadonlyServiceRule;

/**
 * @extends RuleTestCase<ReadonlyServiceRule>
 */
final class ReadonlyServiceRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new ReadonlyServiceRule();
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/data/readonly-service.php'], [
            [
                'Service class App\Service\ReadyForReadonly should be declared readonly.',
                5,
            ],
        ]);
    }
}
