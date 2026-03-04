<?php

namespace App\Controller;

use Symfony\Component\Routing\Attribute\Route;

class PathController
{
    #[Route(path: '/payment_accounts', name: 'bad', methods: 'GET')]
    public function snakeCaseAction(): void // ERROR — payment_accounts
    {
    }

    #[Route(path: '/some_thing/{thingId}/sub_path', name: 'bad2', methods: 'GET')]
    public function multipleSnakeCaseAction(): void // ERROR — some_thing
    {
    }

    #[Route(path: '/paymentAccounts/{accountId}/acceptRate', name: 'good', methods: 'GET')]
    public function camelCaseAction(): void // OK
    {
    }

    #[Route(path: '/things', name: 'simple', methods: 'GET')]
    public function simpleLowercaseAction(): void // OK — single word
    {
    }

    #[Route(path: '/things/{thingId}', name: 'param', methods: 'GET')]
    public function paramSegmentAction(): void // OK — {param} segments skipped
    {
    }

    #[Route(path: '/api/admin/things', name: 'prefix', methods: 'GET')]
    public function prefixAction(): void // OK — api and admin are single words
    {
    }
}
