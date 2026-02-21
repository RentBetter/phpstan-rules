<?php

namespace App\Controller;

use Symfony\Component\Routing\Attribute\Route;

class RouteNameController
{
    #[Route('/things', name: 'getAllThings', methods: 'GET')] // ERROR - doesn't match listThings
    public function listThingsAction(): void
    {
    }

    #[Route('/things/{thingId}', name: 'getThing', methods: 'GET')] // OK - matches
    public function getThingAction(): void
    {
    }
}
