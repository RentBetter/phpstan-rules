<?php

declare(strict_types=1);

namespace PTGS\PHPStanRules\Tests\Rules\Architecture\Data\NoWallClock;

use DateTime;
use DateTimeImmutable;
use Symfony\Component\Clock\ClockInterface;
use Symfony\Component\Clock\DatePoint;

use function Symfony\Component\Clock\now;

final class WallClockReads
{
    public function __construct(
        private readonly ClockInterface $clock,
    ) {}

    public function offending(): void
    {
        $a = new DateTimeImmutable();
        $b = new DateTimeImmutable('now');
        $c = new DateTimeImmutable('today');
        $d = new \DateTimeImmutable('+1 day');
        $e = new DateTime();
        $f = new DatePoint();
        $g = new DatePoint('next monday');
        $h = time();
        $i = microtime(true);
        $j = date('Y-m-d');
        $k = \gmdate('c');
    }

    public function fine(): void
    {
        $a = $this->clock->now();
        $b = now();
        $c = now('+1 day');
        $d = new DateTimeImmutable('2025-01-15');
        $e = new DateTimeImmutable('@1700000000');
        $f = DatePoint::createFromFormat('Y-m-d', '2025-01-15');
        $g = new DateTimeImmutable($this->someString());
        $h = strtotime('tomorrow', 1700000000);
        $i = $this->date();
    }

    private function someString(): string
    {
        return '2025-01-15';
    }

    /** A method called date() is not the function. */
    private function date(): string
    {
        return 'not the wall';
    }
}
