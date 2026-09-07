<?php

namespace App\Controller;

use Symfony\Component\Routing\Attribute\Route;

class FirstController
{
    #[Route('/things', name: 'listThings', methods: 'GET')] // ERROR - name also declared in SecondController
    public function listThingsAction(): void
    {
    }

    #[Route('/things/{thingId}', name: 'getThing', methods: 'GET')] // OK - declared once
    public function getThingAction(): void
    {
    }
}

class SecondController
{
    #[Route('/widgets', name: 'listThings', methods: 'GET')] // ERROR - collides with FirstController
    public function listWidgetsAction(): void
    {
    }
}
