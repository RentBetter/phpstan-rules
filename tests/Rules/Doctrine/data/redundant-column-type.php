<?php

namespace App\Entity;

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

    #[ORM\Column(type: Types::JSON)] // OK - not a scalar default
    private array $data;
}
