<?php

declare(strict_types=1);

namespace PTGS\PHPStanRules\Tests\Rules\Symfony;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PTGS\PHPStanRules\Rules\Symfony\NoJsonDecodeInControllerRule;
use PTGS\PHPStanRules\Tests\Rules\TestGroups;

/** @extends RuleTestCase<NoJsonDecodeInControllerRule> */
final class NoJsonDecodeInControllerRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new NoJsonDecodeInControllerRule(TestGroups::defaultResolver());
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/data/json-decode-in-controller.php'], [
            ['Avoid calling json_decode() in controllers. Use the Form component to bind the request into a typed DTO instead.', 11],
            ['Avoid calling json_decode() in controllers. Use the Form component to bind the request into a typed DTO instead.', 16],
            ['Avoid calling json_decode() in controllers. Use the Form component to bind the request into a typed DTO instead.', 21],
        ]);
    }
}
