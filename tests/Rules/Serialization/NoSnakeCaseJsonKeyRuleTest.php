<?php

declare(strict_types=1);

namespace RentBetter\PHPStanRules\Tests\Rules\Serialization;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use RentBetter\PHPStanRules\Rules\Serialization\NoSnakeCaseJsonKeyRule;

/**
 * @extends RuleTestCase<NoSnakeCaseJsonKeyRule>
 */
final class NoSnakeCaseJsonKeyRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new NoSnakeCaseJsonKeyRule();
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/data/snake-case-json-key.php'], [
            [
                "JSON key 'first_name' in jsonSerialize() uses snake_case. Use camelCase instead.",
                10,
            ],
            [
                "JSON key 'email_address' in jsonSerialize() uses snake_case. Use camelCase instead.",
                12,
            ],
        ]);
    }
}
