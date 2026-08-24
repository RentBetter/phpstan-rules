<?php

namespace App\Controller;

use Symfony\Component\Routing\Attribute\Route;

class PathController
{
    #[Route(path: '/payment_accounts', name: 'bad', methods: 'GET')]
    public function snakeCaseAction(): void // ERROR — payment_accounts (snake)
    {
    }

    #[Route(path: '/some_thing/{thingId}/sub_path', name: 'bad2', methods: 'GET')]
    public function multipleSnakeCaseAction(): void // ERROR — some_thing (snake)
    {
    }

    #[Route(path: '/payment-accounts', name: 'bad3', methods: 'GET')]
    public function kebabCaseAction(): void // ERROR — payment-accounts (kebab)
    {
    }

    #[Route(path: '/ad-spend/{accountId}/accept-rate', name: 'bad4', methods: 'GET')]
    public function multipleKebabCaseAction(): void // ERROR — ad-spend (kebab)
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

    #[Route(path: '/admin/reports.{_format}', name: 'format', methods: 'GET')]
    public function reservedFormatParamAction(): void // OK — {_format} is Symfony's reserved param, not snake_case
    {
    }

    #[Route(path: '/tenancies/{tenancyId}/notice.{_format}', name: 'format2', methods: 'GET')]
    public function reservedFormatParamOnNestedPathAction(): void // OK
    {
    }

    #[Route(path: '/{_locale}/things', name: 'locale', methods: 'GET')]
    public function reservedLocaleParamAction(): void // OK — bare reserved placeholder segment
    {
    }

    #[Route(path: '/bad_thing.{_format}', name: 'stillbad', methods: 'GET')]
    public function snakeCaseLiteralBesideReservedParamAction(): void // ERROR — the literal part is still snake_case
    {
    }
}
