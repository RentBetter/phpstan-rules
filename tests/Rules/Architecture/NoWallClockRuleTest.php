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
    private const string HINT = ' reads the wall clock. Inject Symfony\Component\Clock\ClockInterface and use $this->clock->now(), '
        . 'or call Symfony\Component\Clock\now() where there is no DI, so the time is testable.';

    protected function getRule(): Rule
    {
        return new NoWallClockRule();
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/data/no-wall-clock.php'], [
            ['new DateTimeImmutable()' . self::HINT, 22],
            ["new DateTimeImmutable('now')" . self::HINT, 23],
            ["new DateTimeImmutable('today')" . self::HINT, 24],
            ["new DateTimeImmutable('+1 day')" . self::HINT, 25],
            ['new DateTime()' . self::HINT, 26],
            ['new DatePoint()' . self::HINT, 27],
            ["new DatePoint('next monday')" . self::HINT, 28],
            ['time()' . self::HINT, 29],
            ['microtime()' . self::HINT, 30],
            ['date()' . self::HINT, 31],
            ['gmdate()' . self::HINT, 32],
        ]);
    }
}
