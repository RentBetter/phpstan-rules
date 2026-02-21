<?php

namespace App\Entity;

class RawArrayEntity implements \JsonSerializable
{
    private ?string $name = null;

    public function jsonSerialize(): array
    {
        return [ // ERROR - raw array without filter
            'name' => $this->name,
        ];
    }
}

class FilteredEntity implements \JsonSerializable
{
    private ?string $name = null;

    public function jsonSerialize(): array
    {
        return array_filter_nulls([ // OK - wrapped in filter
            'name' => $this->name,
        ]);
    }
}
