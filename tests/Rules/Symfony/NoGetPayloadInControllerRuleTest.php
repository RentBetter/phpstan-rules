<?php

declare(strict_types=1);

namespace PTGS\PHPStanRules\Tests\Rules\Symfony;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PTGS\PHPStanRules\Rules\Symfony\NoGetPayloadInControllerRule;
use PTGS\PHPStanRules\Tests\Rules\TestGroups;

/** @extends RuleTestCase<NoGetPayloadInControllerRule> */
final class NoGetPayloadInControllerRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new NoGetPayloadInControllerRule(TestGroups::defaultResolver());
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/data/get-payload-in-controller.php'], [
            ['Avoid inspecting request payload in controllers. Use the Form component to bind the request into a typed DTO instead.', 11],
            ['Avoid inspecting request payload in controllers. Use the Form component to bind the request into a typed DTO instead.', 16],
        ]);
    }
}
