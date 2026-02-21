<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;

class FlushService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {}

    public function bad(): void
    {
        $this->em->flush(); // ERROR
    }

    public function ok(): void
    {
        $this->em->persist(new \stdClass());  // OK - not flush
    }
}
