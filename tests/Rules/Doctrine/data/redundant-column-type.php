<?php

namespace App\Entity;

use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class RedundantTypes
{
    #[ORM\Column(type: Types::STRING)] // ERROR - redundant
    private string $name;

    #[ORM\Column(type: Types::INTEGER)] // ERROR - redundant
    private int $count;

    #[ORM\Column(type: Types::BOOLEAN)] // ERROR - redundant
    private bool $active;

    #[ORM\Column(type: Types::FLOAT)] // ERROR - redundant
    private float $rate;

    #[ORM\Column(type: Types::STRING, nullable: true)] // ERROR - type still redundant
    private ?string $nickname;

    #[ORM\Column(type: 'string')] // ERROR - string literal form
    private string $literal;

    #[ORM\Column(type: Types::TEXT)] // OK - TEXT is not the default for string
    private string $description;

    #[ORM\Column(type: Types::SMALLINT)] // OK - SMALLINT is not the default for int
    private int $sortOrder;

    #[ORM\Column] // OK - no type specified
    private string $simple;

    #[ORM\Column(type: Types::JSON)] // ERROR - redundant, Doctrine infers array → JSON
    private array $data;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)] // ERROR - redundant
    private DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)] // ERROR - type still redundant
    private ?DateTimeImmutable $deletedAt;

    #[ORM\Column(type: Types::JSON, options: ['jsonb' => true])] // ERROR - type redundant even with options
    private array $metadata;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)] // OK - DATE_IMMUTABLE is not DATETIME_IMMUTABLE
    private DateTimeImmutable $birthDate;

    #[ORM\Column] // OK - no type specified
    private array $tags;
}
