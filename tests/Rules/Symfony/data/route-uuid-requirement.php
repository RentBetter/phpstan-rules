<?php

namespace App\Controller;

use Symfony\Component\Routing\Attribute\Route;

class UuidController
{
    #[Route('/tenancies/{tenancyId}', name: 'getTenancy', methods: 'GET')] // ERROR - no requirements
    public function getTenancyAction(string $tenancyId): void
    {
    }

    #[Route('/tenancies/{tenancyId}', name: 'updateTenancy', methods: 'PUT', requirements: ['tenancyId' => '[0-9a-f-]+'])] // OK
    public function updateTenancyAction(string $tenancyId): void
    {
    }

    #[Route('/things/{slug}', name: 'getBySlug', methods: 'GET')] // OK - not an *Id param
    public function getBySlugAction(string $slug): void
    {
    }
}
