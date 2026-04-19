<?php

namespace App\Service;

readonly class HasReadonlyProperty
{
    private readonly string $name; // ERROR
}

readonly class HasPlainProperty
{
    private string $name; // OK
}

readonly class HasMultipleReadonlyProperties
{
    private readonly string $slug, $status; // ERROR x2
}

readonly class HasReadonlyPromotedProperty
{
    public function __construct(private readonly string $title) {} // ERROR
}

readonly class HasPlainPromotedProperty
{
    public function __construct(private string $title) {} // OK
}

readonly class HasMixedPromotedProperties
{
    public function __construct(private readonly string $id, private string $name) {} // ERROR on $id only
}

class NonReadonlyClass
{
    public function __construct(private readonly string $name) {} // OK
}
