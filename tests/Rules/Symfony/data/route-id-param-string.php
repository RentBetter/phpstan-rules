<?php

namespace App\Controller;

use Symfony\Component\Routing\Attribute\Route;

class IdParamController
{
    #[Route(path: '/things/{thingId}', name: 'badInt', methods: 'GET')]
    public function intIdAction(int $thingId): void // ERROR — int not string
    {
    }

    #[Route(path: '/things/{thingId}', name: 'badEntity', methods: 'GET')]
    public function entityIdAction(\App\Entity\Thing $thingId): void // ERROR — entity not string
    {
    }

    #[Route(path: '/things/{thingId}', name: 'good', methods: 'GET')]
    public function stringIdAction(string $thingId): void // OK
    {
    }

    #[Route(path: '/things/{slug}', name: 'nonId', methods: 'GET')]
    public function nonIdParamAction(string $slug): void // OK — not an *Id param
    {
    }

    #[Route(path: '/things/{thingId}/items/{itemId}', name: 'multi', methods: 'GET')]
    public function multipleIdsAction(string $thingId, int $itemId): void // ERROR — itemId is int
    {
    }
}
