<?php

namespace App\Controller;

use Symfony\Component\Routing\Attribute\Route;

class IdController
{
    #[Route('/things/{id}', name: 'getThing', methods: 'GET')]
    public function getThingAction(string $id): void // ERROR - $id is too generic
    {
    }

    #[Route('/things/{thingId}', name: 'getSpecificThing', methods: 'GET')]
    public function getSpecificThingAction(string $thingId): void // OK - descriptive name
    {
    }

    public function notARoute(string $id): void // OK - no Route attribute
    {
    }
}
