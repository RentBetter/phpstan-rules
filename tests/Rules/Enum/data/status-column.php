<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

class BadStatusEntity
{
    #[ORM\Column(type: 'string')] // ERROR - status without enumType
    private string $status = 'active';

    #[ORM\Column(type: 'string')] // ERROR - paymentStatus without enumType
    private string $paymentStatus = 'pending';

    #[ORM\Column(type: 'string')] // OK - not a status field
    private string $name = '';

    #[ORM\Column] // OK - a timestamp recording when $status moved, not a status column itself
    private \DateTimeImmutable $statusChangedAt;

    #[ORM\Column(type: 'string')] // OK - records who changed the status, not the status
    private string $statusUpdatedBy = '';
}

class GoodStatusEntity
{
    #[ORM\Column(enumType: SomeStatus::class)] // OK - has enumType
    private SomeStatus $status;

    #[ORM\Column(type: 'string')] // OK - not a status field
    private string $name = '';
}

enum SomeStatus: string
{
    case Active = 'active';
}
