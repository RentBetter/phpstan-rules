<?php

namespace App\Controller;

use Symfony\Component\Routing\Attribute\Route;

class ParamsController
{
    private object $apiRequest;

    #[Route(path: '/things', name: 'twoParams', methods: 'GET')]
    public function twoParamsAction(): void // ERROR — 2 direct params (page, sort)
    {
        $page = $this->apiRequest->query->int('page');
        $sort = $this->apiRequest->query->str('sort');
    }

    #[Route(path: '/things', name: 'threeParams', methods: 'GET')]
    public function threeParamsAction(): void // ERROR — 3 direct params
    {
        $page = $this->apiRequest->query->int('page');
        $sort = $this->apiRequest->query->str('sort');
        $active = $this->apiRequest->request->bool('active');
    }

    #[Route(path: '/things', name: 'shorthand', methods: 'GET')]
    public function shorthandAction(): void // ERROR — 2 params via mixed access
    {
        $page = $this->apiRequest->query->int('page');
        $active = $this->apiRequest->boolQueryParam('active');
    }

    #[Route(path: '/things', name: 'oneParam', methods: 'GET')]
    public function oneParamAction(): void // OK — only 1 param
    {
        $page = $this->apiRequest->query->int('page');
    }

    #[Route(path: '/things', name: 'noParams', methods: 'GET')]
    public function noParamsAction(): void // OK — no params
    {
    }

    #[Route(path: '/things', name: 'sameParamTwice', methods: 'GET')]
    public function sameParamTwiceAction(): void // OK — same param accessed twice = 1 unique
    {
        $page = $this->apiRequest->query->int('page');
        $page2 = $this->apiRequest->query->int('page');
    }

    public function notARoute(): void // OK — no Route attribute
    {
        $this->apiRequest->query->int('page');
        $this->apiRequest->query->str('sort');
    }
}
