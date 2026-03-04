<?php

namespace App\Controller;

use Symfony\Component\Routing\Attribute\Route;

class ThingController
{
    #[Route(path: '/things', name: 'listThings', methods: 'GET')]
    public function missingSpecAction(): void // ERROR — no Spec\Api
    {
    }

    #[Route(path: '/things/{thingId}', name: 'getThing', methods: 'GET')]
    #[\PTGS\PropertyApi\_\Spec\Api('Get a thing.')]
    public function hasSpecFqcnAction(): void // OK
    {
    }
}
