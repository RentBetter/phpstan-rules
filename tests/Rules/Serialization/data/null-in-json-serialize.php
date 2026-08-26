<?php

namespace App\Entity;

class RawArrayEntity implements \JsonSerializable
{
    private ?string $name = null;

    public function jsonSerialize(): array
    {
        return [ // ERROR - 'name' may be null
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

class NonNullableEntity implements \JsonSerializable
{
    private string $name = '';
    private int $count = 0;
    private bool $active = false;

    public function jsonSerialize(): array
    {
        return [ // OK - nothing can be null, the filter would be a no-op
            'name' => $this->name,
            'count' => $this->count,
            'active' => $this->active,
        ];
    }
}

enum Status: string implements \JsonSerializable
{
    case Active = 'active';

    public function jsonSerialize(): array
    {
        return [ // OK - a backed enum's value and name are never null
            'id' => $this->value,
            'name' => $this->name,
        ];
    }
}

class NullableCallEntity implements \JsonSerializable
{
    public function jsonSerialize(): array
    {
        return [ // ERROR - 'id' is fine, 'deletedAt' may be null
            'id' => 'x',
            'deletedAt' => $this->format(null),
        ];
    }

    private function format(?string $value): ?string
    {
        return $value;
    }
}

class ConditionalReturnEntity implements \JsonSerializable
{
    private \DateTimeImmutable $createdAt;

    public function jsonSerialize(): array
    {
        return [ // OK - the conditional return type resolves to string for a non-null argument
            'createdAt' => $this->format($this->createdAt),
        ];
    }

    /**
     * @return ($value is null ? null : string)
     */
    private function format(?\DateTimeInterface $value): ?string
    {
        return $value?->format('c');
    }
}

class ConditionalReturnNullableEntity implements \JsonSerializable
{
    private ?\DateTimeImmutable $deletedAt = null;

    public function jsonSerialize(): array
    {
        return [ // ERROR - the same conditional return type stays nullable for a nullable argument
            'deletedAt' => $this->format($this->deletedAt),
        ];
    }

    /**
     * @return ($value is null ? null : string)
     */
    private function format(?\DateTimeInterface $value): ?string
    {
        return $value?->format('c');
    }
}

class SpreadShapeEntity implements \JsonSerializable
{
    public function jsonSerialize(): array
    {
        return [ // ERROR - a spread value may be null
            ...$this->base(),
            'extra' => 1,
        ];
    }

    /**
     * @return array{id: string, deletedAt: ?string}
     */
    private function base(): array
    {
        return ['id' => 'x', 'deletedAt' => null];
    }
}

class SpreadNonNullableEntity implements \JsonSerializable
{
    public function jsonSerialize(): array
    {
        return [ // OK - every spread value is a string
            ...$this->base(),
            'extra' => 1,
        ];
    }

    /**
     * @return array{id: string, createdAt: string}
     */
    private function base(): array
    {
        return ['id' => 'x', 'createdAt' => 'now'];
    }
}

class MixedEntity implements \JsonSerializable
{
    private mixed $data = null;

    public function jsonSerialize(): array
    {
        return [ // OK - mixed is not treated as nullable, neither inline nor spread
            ...$this->base(),
            'data' => $this->data,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function base(): array
    {
        return [];
    }
}

class LiteralNullEntity implements \JsonSerializable
{
    public function jsonSerialize(): array
    {
        return [ // OK - an always-null literal is a deliberate choice, not a maybe
            'parent' => null,
        ];
    }
}

class NarrowedEntity implements \JsonSerializable
{
    private ?string $name = null;

    public function jsonSerialize(): array
    {
        if (null === $this->name) {
            throw new \LogicException();
        }

        return [ // OK - narrowed to string by the guard above
            'name' => $this->name,
        ];
    }
}

class BranchingEntity implements \JsonSerializable
{
    private ?string $reason = null;

    public function jsonSerialize(): array
    {
        if (null === $this->reason) {
            return [ // OK - nothing nullable on this path
                'type' => 'plain',
            ];
        }

        return [ // OK - reason is a string on this path
            'type' => 'reasoned',
            'reason' => $this->reason,
        ];
    }
}

class LocalVariableEntity implements \JsonSerializable
{
    public function jsonSerialize(): array
    {
        $label = $this->label();

        return [ // ERROR - 'label' may be null
            'label' => $label,
        ];
    }

    private function label(): ?string
    {
        return null;
    }
}

class ListEntity implements \JsonSerializable
{
    private ?string $name = null;

    public function jsonSerialize(): array
    {
        return [ // ERROR - [0] may be null
            $this->name,
        ];
    }
}

class ClosureEntity implements \JsonSerializable
{
    private ?string $name = null;

    public function jsonSerialize(): array
    {
        $build = function(): array {
            return [ // OK - a closure's return is not jsonSerialize's
                'name' => $this->name,
            ];
        };

        return array_filter_nulls($build());
    }
}

class NotJsonSerialize
{
    private ?string $name = null;

    public function toArray(): array
    {
        return [ // OK - not jsonSerialize()
            'name' => $this->name,
        ];
    }
}
