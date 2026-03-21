<?php

namespace App\Controller;

use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/things')] // ERROR - class-level route
class ClassLevelRouteController
{
    #[Route('/{thingId}', name: 'getThing', methods: 'GET')]
    public function getThingAction(string $thingId): void
    {
    }
}

class NoClassLevelRouteController // OK
{
    #[Route('/admin/things/{thingId}', name: 'getThing', methods: 'GET')]
    public function getThingAction(string $thingId): void
    {
    }
}
