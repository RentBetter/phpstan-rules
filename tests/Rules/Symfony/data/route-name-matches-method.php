<?php

namespace App\Controller;

use Symfony\Component\Routing\Attribute\Route;

class RouteNameController
{
    #[Route('/things', name: 'getAllThings', methods: 'GET')] // ERROR - last segment doesn't match listThings
    public function listThingsAction(): void
    {
    }

    #[Route('/things/{thingId}', name: 'getThing', methods: 'GET')] // OK - bare match
    public function getThingAction(): void
    {
    }

    #[Route('/widgets', name: 'admin:listWidgets', methods: 'GET')] // OK - prefix:method
    public function listWidgetsAction(): void
    {
    }

    #[Route('/gadgets', name: 'admin:gadgets:listGadgets', methods: 'GET')] // OK - prefix:subdomain:method
    public function listGadgetsAction(): void
    {
    }

    #[Route('/sprockets', name: 'sprockets:listAll', methods: 'GET')] // ERROR - last segment 'listAll' ≠ 'listSprockets'
    public function listSprocketsAction(): void
    {
    }
}

class InvokableRouteController
{
    #[Route('/invokable', name: 'admin:doSomething', methods: 'POST')]
    public function __invoke(): void // OK - invokable controllers are exempt from name-matches-method
    {
    }
}
