<?php

declare(strict_types=1);

namespace PTGS\PHPStanRules\Tests\Rules\Architecture;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PTGS\PHPStanRules\Rules\Architecture\NoWallClockRule;

/**
 * @extends RuleTestCase<NoWallClockRule>
 */
final class NoWallClockRuleTest extends RuleTestCase
{
    private const string HINT = ' reads the wall clock. Inject Psr\Clock\ClockInterface and use $this->clock->now(), '
        . 'or call Symfony\Component\Clock\Clock::get()->now() where there is no DI, so the time is testable.';

    protected function getRule(): Rule
    {
        return new NoWallClockRule();
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/data/no-wall-clock.php'], [
            ['new DateTimeImmutable()' . self::HINT, 27],
            ["new DateTimeImmutable('now')" . self::HINT, 28],
            ["new DateTimeImmutable('today')" . self::HINT, 29],
            ["new DateTimeImmutable('+1 day')" . self::HINT, 30],
            ['new DateTime()' . self::HINT, 31],
            ['new DatePoint()' . self::HINT, 32],
            ["new DatePoint('next monday')" . self::HINT, 33],
            ["new DateTimeImmutable('10:00')" . self::HINT, 34],
            ["new DateTimeImmutable('15 January')" . self::HINT, 35],
            ['new DateTimeImmutable(self::RETENTION)' . self::HINT, 36],
            ["new DateTimeImmutable(sprintf('-%d days', \$days))" . self::HINT, 37],
            ['new DateTimeImmutable("-{$days} days")' . self::HINT, 38],
            ["new DateTimeImmutable('-' . self::THROTTLE_SECONDS . ' seconds')" . self::HINT, 39],
            ["new DateTimeImmutable('-' . \$days . ' days')" . self::HINT, 40],
            ['time()' . self::HINT, 41],
            ['date()' . self::HINT, 42],
            ['gmdate()' . self::HINT, 43],
            ['date()' . self::HINT, 44],
        ]);
    }
}
