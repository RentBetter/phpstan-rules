<?php

namespace App\Controller;

use Symfony\Component\Routing\Attribute\Route;

class IdController
{
    #[Route('/things/{id}', name: 'getThing', methods: 'GET')] // ERROR - {id} is too generic
    public function getThingAction(string $id): void
    {
    }

    // The path is generic even though the method binds it under another name.
    #[Route('/things/{id}/parts', name: 'getThingParts', methods: 'GET')] // ERROR - {id} is too generic
    public function getThingPartsAction(string $thingId): void
    {
    }

    #[Route('/things/{thingId}/parts/{id}', name: 'getPart', methods: 'GET')] // ERROR - {id} is too generic
    public function getPartAction(string $thingId, string $partId): void
    {
    }

    #[Route('/things/{thingId}', name: 'getSpecificThing', methods: 'GET')]
    public function getSpecificThingAction(string $id): void // ERROR - $id is too generic
    {
    }

    #[Route('/things/{thingId}', name: 'listThings', methods: 'GET')]
    public function listThingsAction(string $thingId): void // OK - descriptive both ways
    {
    }

    // OK - {identifier} is descriptive; it must not match on {id} as a substring.
    #[Route('/things/{identifier}', name: 'findThing', methods: 'GET')]
    public function findThingAction(string $identifier): void
    {
    }

    public function notARoute(string $id): void // OK - no Route attribute
    {
    }
}
