<?php

namespace App\Controller;

use Symfony\Component\Routing\Attribute\Route;

class MethodController
{
    #[Route('/things', name: 'listThings')] // ERROR - no methods:
    public function listThingsAction(): void
    {
    }

    #[Route('/things/{thingId}', name: 'getThing', methods: 'GET')] // OK
    public function getThingAction(string $thingId): void
    {
    }

    #[Route('/things', name: 'createThing', methods: ['POST'])] // OK
    public function createThingAction(): void
    {
    }
}
