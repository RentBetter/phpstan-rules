<?php

namespace App\Service;

class LeadService
{
}

class ThingHelper
{
}

namespace App\Repository;

class LeadRepository
{
}

namespace App\Controller;

use App\Repository\LeadRepository;
use App\Service\LeadService;
use App\Service\ThingHelper;
use Doctrine\ORM\EntityManagerInterface;

class Request
{
}

final class SignupParams
{
    public static function fromRequest(Request $request, LeadService $leadService): self // ERROR - service
    {
        return new self();
    }
}

final class MixedParams
{
    public static function build(
        Request $request,
        LeadService $leadService, // ERROR - service
        LeadRepository $leadRepository, // ERROR - repository
        EntityManagerInterface $entityManager, // ERROR - dbAccess
    ): self {
        return new self();
    }
}

final class NullableServiceParams
{
    public static function maybe(?ThingHelper $helper): self // ERROR - helper is in the service group
    {
        return new self();
    }
}

final class PlainParams
{
    public static function fromRequest(Request $request): self // OK - no dependency, just data
    {
        return new self();
    }
}

final class ScalarParams
{
    public static function of(string $name, int $count, bool $flag): self // OK - scalars
    {
        return new self();
    }
}

final class NoParams
{
    public static function create(): self // OK - takes nothing
    {
        return new self();
    }
}

final class InstanceMethodIsFine
{
    public function resolve(LeadService $leadService): void // OK - not static
    {
    }
}

final class ConstructorIsFine
{
    public function __construct(private LeadService $leadService) // OK - constructor injection
    {
    }
}
