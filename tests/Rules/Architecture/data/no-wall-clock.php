<?php

declare(strict_types=1);

namespace PTGS\PHPStanRules\Tests\Rules\Architecture\Data\NoWallClock;

use DateTime;
use DateTimeImmutable;
use DateTimeZone;
use Symfony\Component\Clock\ClockInterface;
use Symfony\Component\Clock\DatePoint;

use function Symfony\Component\Clock\now;

final class WallClockReads
{
    private const int THROTTLE_SECONDS = 60;

    private const string RETENTION = '-10 years';

    public function __construct(
        private readonly ClockInterface $clock,
    ) {}

    public function offending(int $days, DateTimeZone $tz): void
    {
        $a = new DateTimeImmutable();
        $b = new DateTimeImmutable('now');
        $c = new DateTimeImmutable('today');
        $d = new \DateTimeImmutable('+1 day');
        $e = new DateTime();
        $f = new DatePoint();
        $g = new DatePoint('next monday');
        $h = new DateTimeImmutable('10:00');
        $i = new DateTimeImmutable('15 January');
        $j = new DateTimeImmutable(self::RETENTION);
        $k = new DateTimeImmutable(sprintf('-%d days', $days));
        $l = new DateTimeImmutable("-{$days} days", $tz);
        $m = new DateTimeImmutable('-' . self::THROTTLE_SECONDS . ' seconds');
        $n = new DateTimeImmutable('-' . $days . ' days');
        $o = time();
        $p = date('Y-m-d');
        $q = \gmdate('c');
        $r = date('Y-m-d', null);
    }

    /**
     * @param array{created_at: string} $row
     */
    public function fine(array $row, string $date, int $year, int $timestamp): void
    {
        $a = $this->clock->now();
        $b = now();
        $c = now('+1 day');
        $d = new DateTimeImmutable('2025-01-15');
        $e = new DateTimeImmutable('@1700000000');
        $f = new DateTimeImmutable('January 1 2024');
        $g = DatePoint::createFromFormat('Y-m-d', '2025-01-15');
        $h = new DateTimeImmutable($this->someString());
        $i = new DateTimeImmutable($row['created_at']);
        $j = new DateTimeImmutable($date . ' 10:00');
        $k = new DateTimeImmutable("{$year}-07-01");
        $l = new DateTimeImmutable(sprintf('%d-01-01', $year));
        $m = new DateTimeImmutable('@' . $timestamp);
        $n = strtotime('tomorrow', 1700000000);
        $o = microtime(true);
        $p = date('t', $timestamp);
        $q = date('g:i A', $timestamp);
        $r = idate('Y', $timestamp);
        $s = getdate($timestamp);
        $t = $this->date();
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
