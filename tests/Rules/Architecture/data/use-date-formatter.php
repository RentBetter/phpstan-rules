<?php

namespace App\Service;

class ThingService
{
    public function directFormatOutsideJsonSerialize(\DateTimeImmutable $date): string
    {
        return $date->format('c'); // OK — not inside jsonSerialize
    }

    public function interfaceFormatOutsideJsonSerialize(\DateTimeInterface $date): string
    {
        return $date->format('Y-m-d'); // OK — not inside jsonSerialize
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

class ThingWithJsonSerialize implements \JsonSerializable
{
    public function __construct(
        private \DateTimeImmutable $createdAt,
        private \DateTimeInterface $updatedAt,
    ) {}

    public function jsonSerialize(): mixed
    {
        return [
            'createdAt' => $this->createdAt->format('c'), // ERROR
            'updatedAt' => $this->updatedAt->format('Y-m-d'), // ERROR
            'timestamp' => $this->createdAt->getTimestamp(), // OK — not format()
        ];
    }
}
