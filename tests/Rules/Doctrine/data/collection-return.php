<?php

namespace App\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

class User
{
    /** @var Collection<int, string> */
    private Collection $items;

    public function __construct()
    {
        $this->items = new ArrayCollection();
    }

    /** @return Collection<int, string> */
    public function getItems(): Collection
    {
        return $this->items;
    }

    /** @return array<int, string> */
    public function getItemsArray(): array
    {
        return $this->items->toArray();
    }

    /** @return Collection<int, string> */
    private function getItemsPrivate(): Collection
    {
        return $this->items;
    }
}

namespace App\Model;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

class NotAnEntity
{
    /** @var Collection<int, string> */
    private Collection $items;

    public function __construct()
    {
        $this->items = new ArrayCollection();
    }

    /** @return Collection<int, string> */
    public function getItems(): Collection
    {
        return $this->items;
    }
}
