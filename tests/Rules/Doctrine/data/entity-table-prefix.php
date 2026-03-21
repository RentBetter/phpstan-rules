<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'things')] // ERROR - missing tbl_ prefix
#[ORM\Entity]
class BadTableName
{
    #[ORM\Id]
    #[ORM\Column]
    private int $id;
}

#[ORM\Entity] // ERROR - missing #[ORM\Table] entirely
class MissingTable
{
    #[ORM\Id]
    #[ORM\Column]
    private int $id;
}

#[ORM\Table(name: 'tbl_things')] // OK
#[ORM\Entity]
class GoodTableName
{
    #[ORM\Id]
    #[ORM\Column]
    private int $id;
}

class NotAnEntity // OK - not an entity
{
}
