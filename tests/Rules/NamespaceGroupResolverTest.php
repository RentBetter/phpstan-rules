<?php

declare(strict_types=1);

namespace RentBetter\PHPStanRules\Tests\Rules;

use PHPStan\Type\ObjectType;
use PHPUnit\Framework\TestCase;
use RentBetter\PHPStanRules\Rules\NamespaceGroupResolver;

/**
 * Unit tests for FQCN-string matching paths. The type-aware `inGroupByType()` subtype
 * matching is exercised via {@see \RentBetter\PHPStanRules\Tests\Rules\Architecture\ForbiddenDependencyRuleTest},
 * which runs inside PHPStan's `RuleTestCase` (it bootstraps the reflection provider
 * required for `ObjectType::isSuperTypeOf()`).
 */
final class NamespaceGroupResolverTest extends TestCase
{
    private function resolver(): NamespaceGroupResolver
    {
        return new NamespaceGroupResolver([
            'controller' => ['~\\\\Controller\\\\~'],
            'service'    => ['~\\\\Services?\\\\~', '~\\\\Helpers?\\\\~'],
            'repository' => ['~\\\\Repository\\\\~'],
            'dbAccess'   => [
                'Doctrine\ORM\EntityManagerInterface',
                'Doctrine\DBAL\Connection',
            ],
        ]);
    }

    public function testRegexMatch(): void
    {
        self::assertTrue($this->resolver()->inGroup('App\\Controller\\FooController', 'controller'));
        self::assertTrue($this->resolver()->inGroup('PTGS\\Foo\\Service\\BarService', 'service'));
        self::assertTrue($this->resolver()->inGroup('PTGS\\Foo\\Helper\\BazHelper', 'service'));
    }

    public function testRegexNoMatch(): void
    {
        self::assertFalse($this->resolver()->inGroup('App\\Entity\\Foo', 'controller'));
        self::assertFalse($this->resolver()->inGroup('App\\Entity\\Foo', 'service'));
    }

    public function testLiteralFqcnExactMatch(): void
    {
        self::assertTrue($this->resolver()->inGroup('Doctrine\\ORM\\EntityManagerInterface', 'dbAccess'));
        self::assertTrue($this->resolver()->inGroup('Doctrine\\DBAL\\Connection', 'dbAccess'));
        self::assertFalse($this->resolver()->inGroup('Doctrine\\DBAL\\OtherClass', 'dbAccess'));
    }

    public function testInGroupByTypeMatchesRegex(): void
    {
        // Regex path doesn't hit isSuperTypeOf, safe to test in pure unit test.
        $type = new ObjectType('App\\Repository\\FooRepository');
        self::assertTrue($this->resolver()->inGroupByType($type, 'repository'));
    }

    public function testGetGroupsReturnsAllMatches(): void
    {
        // A class in App\Service\Controller\FooController matches both service AND controller groups.
        $groups = $this->resolver()->getGroups('App\\Service\\Controller\\FooController');
        sort($groups);
        self::assertSame(['controller', 'service'], $groups);
    }

    public function testGetGroupsReturnsEmptyForUnmatched(): void
    {
        self::assertSame([], $this->resolver()->getGroups('App\\Other\\Foo'));
    }

    public function testUnknownGroupThrows(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown namespace group "missing"');
        $this->resolver()->inGroup('App\\Foo', 'missing');
    }
}
