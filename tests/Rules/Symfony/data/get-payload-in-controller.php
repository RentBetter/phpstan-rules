<?php

namespace App\Controller;

class PayloadController
{
    private object $apiRequest;

    public function badJsonPayload(): void
    {
        $this->apiRequest->getJsonPayload(); // ERROR
    }

    public function badGetPayload(): void
    {
        $this->apiRequest->getPayload(); // ERROR
    }

    public function okMethod(): void
    {
        $this->apiRequest->getAccount(); // OK — not a payload method
    }
}

namespace App\Service;

class PayloadService
{
    private object $apiRequest;

    public function okInService(): void
    {
        $this->apiRequest->getJsonPayload(); // OK — not in a controller
    }
}
