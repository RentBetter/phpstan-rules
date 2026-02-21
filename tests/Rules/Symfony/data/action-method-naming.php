<?php

namespace App\Controller;

use Symfony\Component\Routing\Attribute\Route;

class NamingController
{
    #[Route('/things', name: 'listThings', methods: 'GET')]
    public function listThings(): void // ERROR - should end with Action
    {
    }

    #[Route('/things/{thingId}', name: 'getThing', methods: 'GET')]
    public function getThingAction(): void // OK
    {
    }
}
