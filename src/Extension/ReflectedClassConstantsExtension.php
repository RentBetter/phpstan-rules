<?php

declare(strict_types=1);

namespace PTGS\PHPStanRules\Extension;

use PHPStan\Reflection\ClassConstantReflection;
use PHPStan\Rules\Constants\AlwaysUsedClassConstantsExtension;

/**
 * Marks constants that are read through reflection as used.
 *
 * A constant fetched by name — `$reflection->getConstant('NAMES')`, a lookup keyed on a string,
 * a serializer reading it out of the class — has no reference phpstan can follow, so every
 * private one is reported unused. Suppressing that at each declaration spreads the same
 * @phpstan-ignore across the codebase and buries why the constant is not dead.
 *
 * Configure the constants this applies to, optionally narrowed to the classes that participate
 * in the reflective read:
 *
 *     parameters:
 *         ptgs:
 *             reflectedConstants:
 *                 - name: NAMES
 *                   declaredBy: 'App\Entity\SerializableEnumTrait'
 *
 * `declaredBy` accepts a trait, interface or parent class; omit it to match the constant name
 * anywhere, which is blunt enough to be worth avoiding.
 */
final class ReflectedClassConstantsExtension implements AlwaysUsedClassConstantsExtension
{
    /**
     * @param list<array{name: string, declaredBy: string|null}> $reflectedConstants
     */
    public function __construct(
        private readonly array $reflectedConstants = [],
    ) {}

    public function isAlwaysUsed(ClassConstantReflection $constant): bool
    {
        foreach ($this->reflectedConstants as $reflected) {
            if ($reflected['name'] !== $constant->getName()) {
                continue;
            }

            $declaredBy = $reflected['declaredBy'] ?? null;
            if (null === $declaredBy) {
                return true;
            }

            $class = $constant->getDeclaringClass();
            if ($class->hasTraitUse($declaredBy)
                || $class->implementsInterface($declaredBy)
                || $class->isSubclassOf($declaredBy)
            ) {
                return true;
            }
        }

        return false;
    }
}
