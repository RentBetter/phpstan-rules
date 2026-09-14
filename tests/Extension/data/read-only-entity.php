<?php

declare(strict_types=1);

namespace ReadOnlyEntityFixture;

use Doctrine\ORM\Mapping as ORM;

/** Rows are written by SQL; PHP only ever hydrates these. */
#[ORM\Entity(readOnly: true)]
class ReadOnlyRecord
{
    #[ORM\Id]
    #[ORM\Column]
    private string $id;

    #[ORM\ManyToOne(targetEntity: Owner::class)]
    private ?Owner $owner = null;

    private int $scratch = 0;

    public function __construct() {}

    public function getId(): string
    {
        return $this->id;
    }

    public function getOwner(): ?Owner
    {
        return $this->owner;
    }

    public function getScratch(): int
    {
        return $this->scratch;
    }
}

#[ORM\Entity]
class WritableRecord
{
    #[ORM\Id]
    #[ORM\Column]
    private string $id;

    public function getId(): string
    {
        return $this->id;
    }
}

#[ORM\Entity(readOnly: false)]
class ExplicitlyWritableRecord
{
    #[ORM\Id]
    #[ORM\Column]
    private string $id;

    public function getId(): string
    {
        return $this->id;
    }
}

class NotAnEntity
{
    #[ORM\Column]
    private string $id;

    public function getId(): string
    {
        return $this->id;
    }
}

class Owner
{
}
