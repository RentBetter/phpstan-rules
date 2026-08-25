<?php

namespace ReflectedConstantsFixture;

trait SerializableEnumTrait
{
    public function label(): string
    {
        // Read by name, so nothing references NAMES directly.
        return (new \ReflectionClass($this))->getConstant('NAMES')[$this->name] ?? $this->name;
    }
}

enum ThemedStatus: int
{
    use SerializableEnumTrait;

    private const array NAMES = ['PENDING' => 'Awaiting Payment'];
    private const string ID_PREFIX = 'THEMED_STATUS_';

    case PENDING = 0;
}

class Unrelated
{
    private const array NAMES = ['a' => 'b'];
}
