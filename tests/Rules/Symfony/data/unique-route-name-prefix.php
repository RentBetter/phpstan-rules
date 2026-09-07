<?php

namespace App\Controller;

use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin', name: 'admin_')]
class AdminController
{
    #[Route('/things', name: 'listThings', methods: 'GET')] // OK - registers as admin_listThings
    public function listThingsAction(): void
    {
    }

    #[Route('/things/{thingId}', name: 'getThing', methods: 'GET')] // ERROR - registers as admin_getThing, which FlatController declares outright
    public function getThingAction(): void
    {
    }
}

#[Route('/api', name: 'api_')]
class ApiController
{
    #[Route('/things', name: 'listThings', methods: 'GET')] // OK - registers as api_listThings, distinct from admin_listThings
    public function listThingsAction(): void
    {
    }
}

#[Route('/reports')]
class ReportController
{
    #[Route('/reports', name: 'listReports', methods: 'GET')] // OK - a class-level #[Route] without a name adds no prefix
    public function listReportsAction(): void
    {
    }
}

class FlatController
{
    #[Route('/admin/things/{thingId}', name: 'admin_getThing', methods: 'GET')] // ERROR - collides with AdminController's prefixed getThing
    public function adminGetThingAction(): void
    {
    }

    #[Route('/ping', name: 'ping', methods: 'GET')] // ERROR - collides with the invokable PingController
    public function pingAction(): void
    {
    }
}

#[Route('/ping', name: 'ping', methods: 'GET')] // ERROR - on an invokable class with no method routes, the class-level attribute is the route
class PingController
{
    public function __invoke(): void
    {
    }
}

#[Route('/v2', name: 'v2_')]
class VersionedPingController
{
    #[Route('/ping', name: 'ping', methods: 'GET')] // OK - registers as v2_ping; with a method route present the class attribute is only a prefix
    public function __invoke(): void
    {
    }
}
