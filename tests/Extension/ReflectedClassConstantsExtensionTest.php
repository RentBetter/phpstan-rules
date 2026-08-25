<?php

declare(strict_types=1);

namespace PTGS\PHPStanRules\Tests\Extension;

use PHPStan\Reflection\ClassConstantReflection;
use PHPStan\Testing\PHPStanTestCase;
use PTGS\PHPStanRules\Extension\ReflectedClassConstantsExtension;

final class ReflectedClassConstantsExtensionTest extends PHPStanTestCase
{
    private const string TRAIT = 'ReflectedConstantsFixture\SerializableEnumTrait';
    private const string ENUM = 'ReflectedConstantsFixture\ThemedStatus';
    private const string UNRELATED = 'ReflectedConstantsFixture\Unrelated';

    public static function setUpBeforeClass(): void
    {
        require_once __DIR__ . '/data/reflected-constants.php';
    }

    public function testMarksTheConfiguredConstantAsUsedWhenTheClassUsesTheTrait(): void
    {
        $extension = new ReflectedClassConstantsExtension([['name' => 'NAMES', 'declaredBy' => self::TRAIT]]);

        self::assertTrue($extension->isAlwaysUsed($this->constant(self::ENUM, 'NAMES')));
    }

    /** The same constant name on a class with no part in the reflective read is still unused. */
    public function testLeavesTheSameConstantAloneOnAnUnrelatedClass(): void
    {
        $extension = new ReflectedClassConstantsExtension([['name' => 'NAMES', 'declaredBy' => self::TRAIT]]);

        self::assertFalse($extension->isAlwaysUsed($this->constant(self::UNRELATED, 'NAMES')));
    }

    public function testLeavesOtherConstantsAloneOnAMatchingClass(): void
    {
        $extension = new ReflectedClassConstantsExtension([['name' => 'NAMES', 'declaredBy' => self::TRAIT]]);

        self::assertFalse($extension->isAlwaysUsed($this->constant(self::ENUM, 'ID_PREFIX')));
    }

    public function testOmittingDeclaredByMatchesTheConstantNameAnywhere(): void
    {
        $extension = new ReflectedClassConstantsExtension([['name' => 'NAMES', 'declaredBy' => null]]);

        self::assertTrue($extension->isAlwaysUsed($this->constant(self::UNRELATED, 'NAMES')));
    }

    public function testAnEmptyConfigurationMarksNothing(): void
    {
        self::assertFalse((new ReflectedClassConstantsExtension())->isAlwaysUsed($this->constant(self::ENUM, 'NAMES')));
    }

    private function constant(string $class, string $name): ClassConstantReflection
    {
        return self::createReflectionProvider()->getClass($class)->getConstant($name);
    }
}
