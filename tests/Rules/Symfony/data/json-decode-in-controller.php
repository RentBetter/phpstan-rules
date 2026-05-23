<?php

namespace App\Controller;

class JsonDecodeController
{
    private object $request;

    public function badDecode(): void
    {
        $data = json_decode('{}', true); // ERROR
    }

    public function badDecodeFromRequest(): void
    {
        $data = json_decode($this->request->getContent(), true); // ERROR
    }

    public function badDecodeFullyQualified(): void
    {
        $data = \json_decode('{}'); // ERROR
    }

    public function okMethod(): void
    {
        $obj = new \stdClass(); // OK — not json_decode
    }
}

namespace App\Service;

class JsonDecodeService
{
    public function okInService(): void
    {
        $data = json_decode('{}', true); // OK — not in a controller
    }
}
