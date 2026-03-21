<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'tbl_things')]
#[ORM\Entity] // ERROR - missing ChangeTrackingPolicy
class MissingPolicy
{
    #[ORM\Id]
    #[ORM\Column]
    private int $id;
}

#[ORM\Table(name: 'tbl_good_things')]
#[ORM\ChangeTrackingPolicy('DEFERRED_EXPLICIT')]
#[ORM\Entity] // OK
class WithPolicy
{
    #[ORM\Id]
    #[ORM\Column]
    private int $id;
}

#[ORM\Table(name: 'tbl_bad_things')]
#[ORM\ChangeTrackingPolicy('DEFERRED_IMPLICIT')] // ERROR - wrong policy
#[ORM\Entity]
class WrongPolicy
{
    #[ORM\Id]
    #[ORM\Column]
    private int $id;
}

class NotAnEntity // OK - not an entity
{
}
