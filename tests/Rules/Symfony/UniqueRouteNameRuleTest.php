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
            [self::collision('listThings', $file, 22), 9],
            [self::collision('listThings', $file, 9), 22],
        ]);
    }

    public function testResolvesNamesThroughClassLevelRouteAttributes(): void
    {
        $file = __DIR__ . '/data/unique-route-name-prefix.php';

        $this->analyse([$file], [
            [self::collision('admin_getThing', $file, 41), 15],
            [self::collision('admin_getThing', $file, 15), 41],
            [self::collision('ping', $file, 52), 46],
            [self::collision('ping', $file, 46), 52],
        ]);
    }

    private static function collision(string $name, string $otherFile, int $otherLine): string
    {
        return \sprintf(
            "Route name '%s' is also declared in %s:%d — Symfony keeps only the last-registered route and silently 404s the rest. Route names are global: disambiguate with a domain prefix.",
            $name,
            $otherFile,
            $otherLine,
        );
    }
}
