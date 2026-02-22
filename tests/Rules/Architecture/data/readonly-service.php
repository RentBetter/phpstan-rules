<?php

namespace App\Service;

class ReadyForReadonly // ERROR - all properties are readonly, no parent
{
    public function __construct(
        private readonly string $name,
    ) {}
}

readonly class AlreadyReadonly // OK
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

class HasMutableProperty // OK - has non-readonly property, can't just add readonly
{
    private string $mutable = '';

    public function __construct(
        private readonly string $name,
    ) {}
}

class ExtendsNonReadonly extends HasMutableProperty // OK - parent is not readonly
{
    public function __construct(
        private readonly string $extra,
    ) {
        parent::__construct('test');
    }
}

class NoProperties // OK - no properties at all, nothing to gain
{
    public function doStuff(): void {}
}

class OnlyNonPromoted // OK - no promoted/declared properties
{
    public function __construct(string $name) {}
}

namespace App\Controller;

class MyController // OK - excluded pattern
{
    public function __construct(
        private readonly string $name,
    ) {}
}

namespace App\Entity;

class MyEntity // OK - excluded pattern
{
}

namespace Other;

class OtherService // OK - not in App\ namespace
{
    public function __construct(
        private readonly string $name,
    ) {}
}
