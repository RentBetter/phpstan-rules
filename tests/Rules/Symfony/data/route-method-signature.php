<?php

namespace App\Controller;

use Symfony\Component\Routing\Attribute\Route;

class ApiRequest {}
class ApiResponse {}
class ApiStreamedResponse {}

class SignatureController
{
    #[Route(path: '/things', name: 'noParams', methods: 'GET')]
    public function noParamsAction(): ApiResponse // ERROR — no ApiRequest param
    {
        return new ApiResponse();
    }

    #[Route(path: '/things', name: 'wrongFirst', methods: 'GET')]
    public function wrongFirstAction(string $id): ApiResponse // ERROR — first param not ApiRequest
    {
        return new ApiResponse();
    }

    #[Route(path: '/things', name: 'noReturn', methods: 'GET')]
    public function noReturnAction(ApiRequest $apiRequest) // ERROR — no return type
    {
    }

    #[Route(path: '/things', name: 'wrongReturn', methods: 'GET')]
    public function wrongReturnAction(ApiRequest $apiRequest): array // ERROR — wrong return type
    {
        return [];
    }

    #[Route(path: '/things', name: 'good', methods: 'GET')]
    public function goodAction(ApiRequest $apiRequest): ApiResponse // OK
    {
        return new ApiResponse();
    }

    #[Route(path: '/things/stream', name: 'streamed', methods: 'GET')]
    public function streamedAction(ApiRequest $apiRequest): ApiStreamedResponse // OK
    {
        return new ApiStreamedResponse();
    }

    public function notARouteMethod(): void // OK — no Route attribute
    {
    }
}
