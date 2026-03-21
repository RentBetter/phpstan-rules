<?php

declare(strict_types=1);

namespace RentBetter\PHPStanRules\Tests\Rules\Symfony;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use RentBetter\PHPStanRules\Rules\Symfony\NoClassLevelRouteRule;

/**
 * @extends RuleTestCase<NoClassLevelRouteRule>
 */
final class NoClassLevelRouteRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new NoClassLevelRouteRule();
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/data/no-class-level-route.php'], [
            [
                'Class ClassLevelRouteController has a class-level #[Route] attribute. Define the full route path on each action method instead — class-level prefixes hide the real path and make routes unsearchable.',
                7,
            ],
        ]);
    }
}
