<?php

namespace App\Service;

class ThingService
{
    public function badSaveFalse(object $entity, bool $save = false): void // ERROR
    {
    }

    public function badFlushFalse(object $entity, bool $flush = false): void // ERROR
    {
    }

    public function goodSaveTrue(object $entity, bool $save = true): void // OK
    {
    }

    public function goodFlushTrue(object $entity, bool $flush = true): void // OK
    {
    }

    public function goodSaveRequired(object $entity, bool $save): void // OK — required
    {
    }

    private function privateSaveFalse(object $entity, bool $save = false): void // OK — private
    {
    }

    protected function protectedSaveFalse(object $entity, bool $save = false): void // OK — protected
    {
    }

    public function notBoolSave(object $entity, string $save = 'false'): void // OK — not bool
    {
    }

    public function differentParamName(object $entity, bool $persist = false): void // OK — not save/flush
    {
    }
}
