<?php

declare(strict_types=1);

namespace PTGS\PHPStanRules\Extension;

use PHPStan\Reflection\PropertyReflection;
use PHPStan\Rules\Properties\ReadWritePropertiesExtension;

use function in_array;

/**
 * Doctrine hydrates the mapped properties of an entity marked `#[ORM\Entity(readOnly: true)]`,
 * and its unit of work never writes one back — the rows come and go by SQL, or are only ever
 * read. Nothing in PHP has to assign those properties, so PHPStan must not report them as
 * "never written, only read".
 *
 * phpstan-doctrine says the same, but only for an entity with no constructor, and a codebase
 * whose entities all declare one (so `static::create()` works everywhere) never qualifies.
 * This reads the mapping straight off the attributes and applies regardless of the constructor.
 */
final class ReadOnlyEntityPropertiesExtension implements ReadWritePropertiesExtension
{
    private const string ENTITY_ATTRIBUTE = 'Doctrine\ORM\Mapping\Entity';

    /** @var list<string> */
    private const array MAPPING_ATTRIBUTES = [
        'Doctrine\ORM\Mapping\Column',
        'Doctrine\ORM\Mapping\Embedded',
        'Doctrine\ORM\Mapping\ManyToOne',
        'Doctrine\ORM\Mapping\ManyToMany',
        'Doctrine\ORM\Mapping\OneToOne',
        'Doctrine\ORM\Mapping\OneToMany',
    ];

    public function isAlwaysRead(PropertyReflection $property, string $propertyName): bool
    {
        return false;
    }

    public function isAlwaysWritten(PropertyReflection $property, string $propertyName): bool
    {
        return $this->isMappedOnReadOnlyEntity($property, $propertyName);
    }

    public function isInitialized(PropertyReflection $property, string $propertyName): bool
    {
        return $this->isMappedOnReadOnlyEntity($property, $propertyName);
    }

    private function isMappedOnReadOnlyEntity(PropertyReflection $property, string $propertyName): bool
    {
        $class = $property->getDeclaringClass()->getNativeReflection();
        if (!$class->hasProperty($propertyName)) {
            return false;
        }

        $readOnly = false;
        foreach ($class->getAttributes() as $attribute) {
            if (self::ENTITY_ATTRIBUTE === $attribute->getName()) {
                $readOnly = true === ($attribute->getArguments()['readOnly'] ?? false);
            }
        }
        if (!$readOnly) {
            return false;
        }

        foreach ($class->getProperty($propertyName)->getAttributes() as $attribute) {
            if (in_array($attribute->getName(), self::MAPPING_ATTRIBUTES, true)) {
                return true;
            }
        }

        return false;
    }
}
