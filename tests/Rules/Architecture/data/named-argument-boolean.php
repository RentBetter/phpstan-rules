<?php

namespace App\Service;

class Caller
{
    public function doStuff(CalledService $service): void
    {
        $service->save(new \stdClass(), true); // ERROR - positional bool
        $service->save(new \stdClass(), flush: true); // OK - named
        $service->process('hello'); // OK - no bool
        \in_array('a', ['a', 'b'], true); // OK - built-in function, not a method call
    }
}

class CalledService
{
    public function save(object $entity, bool $flush = true): void {}
    public function process(string $value): void {}
}
