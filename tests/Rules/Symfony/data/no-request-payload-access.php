<?php

namespace App\Anywhere;

use Symfony\Component\HttpFoundation\Request;

class SomeService
{
    public function badToArray(Request $request): void
    {
        $request->toArray(); // ERROR
    }

    public function badGetPayload(Request $request): void
    {
        $request->getPayload(); // ERROR
    }

    public function badGetContent(Request $request): void
    {
        $request->getContent(); // ERROR
    }

    public function okMethods(Request $request): void
    {
        $request->getMethod();
        $request->getPathInfo();
        $request->headers->get('X-Whatever');
    }
}

namespace App\Other;

class NotARequest
{
    public function toArray(): array { return []; }
    public function getPayload(): array { return []; }
    public function getContent(): string { return ''; }
}

class CallerOfNotARequest
{
    public function ok(NotARequest $thing): void
    {
        $thing->toArray();
        $thing->getPayload();
        $thing->getContent();
    }
}
