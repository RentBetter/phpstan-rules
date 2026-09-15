<?php

namespace App\Entity;

class SnakeEntity implements \JsonSerializable
{
    public function jsonSerialize(): array
    {
        return [
            'first_name' => 'John', // ERROR
            'lastName' => 'Doe', // OK
            'email_address' => 'j@d.com', // ERROR
            '_debug' => ['trend' => 'up'], // OK - leading underscore marks an envelope key, not a word boundary
        ];
    }
}

class GoodEntity implements \JsonSerializable
{
    public function jsonSerialize(): array
    {
        return [
            'firstName' => 'John', // OK
            'lastName' => 'Doe', // OK
        ];
    }
}
