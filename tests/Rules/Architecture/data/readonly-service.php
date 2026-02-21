<?php

namespace App\Service;

class BadService // ERROR - not readonly
{
    public function __construct(
        private readonly string $name,
    ) {}
}

readonly class GoodService // OK
{
    public function __construct(
        private string $name,
    ) {}
}

abstract class AbstractService // OK - abstract
{
    public function __construct(
        private readonly string $name,
    ) {}
}

namespace App\Controller;

class MyController // OK - excluded pattern
{
    public function __construct() {}
}

namespace App\Entity;

class MyEntity // OK - excluded pattern
{
}

namespace Other;

class OtherService // OK - not in App\ namespace
{
}
