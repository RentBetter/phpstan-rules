<?php

declare(strict_types=1);

namespace PTGS\PHPStanRules\Tests\Rules\Symfony;

use PHPStan\Collectors\Collector;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PTGS\PHPStanRules\Rules\Symfony\RouteNameCollector;
use PTGS\PHPStanRules\Rules\Symfony\UniqueRouteNameRule;

/**
 * @extends RuleTestCase<UniqueRouteNameRule>
 */
final class UniqueRouteNameRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new UniqueRouteNameRule();
    }

    /**
     * @return list<Collector<\PhpParser\Node, mixed>>
     */
    protected function getCollectors(): array
    {
        return [new RouteNameCollector()];
    }

    public function testFlagsBothSitesOfACollision(): void
    {
        $file = __DIR__ . '/data/unique-route-name.php';

        $this->analyse([$file], [
            [
                \sprintf(
                    "Route name 'listThings' is also declared in %s:22 — Symfony keeps only the last-registered route and silently 404s the rest. Route names are global: disambiguate with a domain prefix.",
                    $file,
                ),
                9,
            ],
            [
                \sprintf(
                    "Route name 'listThings' is also declared in %s:9 — Symfony keeps only the last-registered route and silently 404s the rest. Route names are global: disambiguate with a domain prefix.",
                    $file,
                ),
                22,
            ],
        ]);
    }
}
