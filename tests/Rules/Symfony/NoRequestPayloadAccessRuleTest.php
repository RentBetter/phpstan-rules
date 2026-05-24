<?php

declare(strict_types=1);

namespace PTGS\PHPStanRules\Tests\Rules\Symfony;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PTGS\PHPStanRules\Rules\Symfony\NoRequestPayloadAccessRule;

/** @extends RuleTestCase<NoRequestPayloadAccessRule> */
final class NoRequestPayloadAccessRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new NoRequestPayloadAccessRule();
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/data/no-request-payload-access.php'], [
            ['Avoid Request::toArray() — use the Form component to bind the request into a typed DTO instead.', 11],
            ['Avoid Request::getPayload() — use the Form component to bind the request into a typed DTO instead.', 16],
            ['Avoid Request::getContent() — use the Form component to bind the request into a typed DTO instead.', 21],
        ]);
    }
}
