<?php

namespace App\Service;

class ThingService
{
    public function badFormat(\DateTimeImmutable $date): string
    {
        return $date->format('c'); // ERROR
    }

    public function badFormatInterface(\DateTimeInterface $date): string
    {
        return $date->format('Y-m-d'); // ERROR
    }

    public function goodNoFormat(\DateTimeImmutable $date): int
    {
        return $date->getTimestamp(); // OK — not format()
    }

    public function goodStringFormat(): string
    {
        $formatter = new \IntlDateFormatter('en_AU');

        return $formatter->format(time()); // OK — not DateTimeInterface
    }
}
