<?php

declare(strict_types=1);

namespace PTGS\PHPStanRules\Tests\Extension;

use PHPStan\Reflection\PropertyReflection;
use PHPStan\Testing\PHPStanTestCase;
use PTGS\PHPStanRules\Extension\ReadOnlyEntityPropertiesExtension;

final class ReadOnlyEntityPropertiesExtensionTest extends PHPStanTestCase
{
    private const string READ_ONLY = 'ReadOnlyEntityFixture\ReadOnlyRecord';
    private const string WRITABLE = 'ReadOnlyEntityFixture\WritableRecord';
    private const string EXPLICITLY_WRITABLE = 'ReadOnlyEntityFixture\ExplicitlyWritableRecord';
    private const string NOT_AN_ENTITY = 'ReadOnlyEntityFixture\NotAnEntity';

    public static function setUpBeforeClass(): void
    {
        require_once __DIR__ . '/data/read-only-entity.php';
    }

    public function testAMappedColumnOnAReadOnlyEntityIsWrittenAndInitialised(): void
    {
        $extension = new ReadOnlyEntityPropertiesExtension();
        $property = $this->property(self::READ_ONLY, 'id');

        self::assertTrue($extension->isAlwaysWritten($property, 'id'));
        self::assertTrue($extension->isInitialized($property, 'id'));
    }

    public function testAMappedAssociationOnAReadOnlyEntityIsWritten(): void
    {
        self::assertTrue((new ReadOnlyEntityPropertiesExtension())->isAlwaysWritten($this->property(self::READ_ONLY, 'owner'), 'owner'));
    }

    /** An unmapped property is not hydrated, so whatever PHP does with it still counts. */
    public function testAnUnmappedPropertyOnAReadOnlyEntityIsLeftAlone(): void
    {
        self::assertFalse((new ReadOnlyEntityPropertiesExtension())->isAlwaysWritten($this->property(self::READ_ONLY, 'scratch'), 'scratch'));
    }

    public function testAWritableEntityIsLeftAlone(): void
    {
        $extension = new ReadOnlyEntityPropertiesExtension();

        self::assertFalse($extension->isAlwaysWritten($this->property(self::WRITABLE, 'id'), 'id'));
        self::assertFalse($extension->isAlwaysWritten($this->property(self::EXPLICITLY_WRITABLE, 'id'), 'id'));
    }

    public function testAMappedPropertyOutsideAnEntityIsLeftAlone(): void
    {
        self::assertFalse((new ReadOnlyEntityPropertiesExtension())->isAlwaysWritten($this->property(self::NOT_AN_ENTITY, 'id'), 'id'));
    }

    /** Reads stay PHPStan's own business — the extension only ever vouches for writes. */
    public function testItNeverClaimsAPropertyIsRead(): void
    {
        self::assertFalse((new ReadOnlyEntityPropertiesExtension())->isAlwaysRead($this->property(self::READ_ONLY, 'id'), 'id'));
    }

    private function property(string $class, string $name): PropertyReflection
    {
        return self::createReflectionProvider()->getClass($class)->getNativeProperty($name);
    }
}
