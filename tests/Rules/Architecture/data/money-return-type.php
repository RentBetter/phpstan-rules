<?php

namespace App\Service;

class MoneyModelV2 {}
interface MoneyInterface {}

class MoneyService
{
    public function badReturn(): MoneyModelV2 // ERROR
    {
        return new MoneyModelV2();
    }

    public function badNullableReturn(): ?MoneyModelV2 // ERROR
    {
        return null;
    }

    public function goodReturn(): MoneyInterface // OK
    {
        return new MoneyModelV2();
    }

    public function goodNullableReturn(): ?MoneyInterface // OK
    {
        return null;
    }

    private function privateReturn(): MoneyModelV2 // OK — private
    {
        return new MoneyModelV2();
    }

    public function otherReturn(): string // OK — not money
    {
        return '';
    }
}
