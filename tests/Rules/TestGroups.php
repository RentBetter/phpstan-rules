<?php

declare(strict_types=1);

namespace RentBetter\PHPStanRules\Tests\Rules;

use RentBetter\PHPStanRules\Rules\NamespaceGroupResolver;

/**
 * Default namespace groups used across rule tests. Mirrors the defaults declared
 * in `extension.neon` so tests exercise the same matching behaviour consumers get.
 */
final class TestGroups
{
    public static function defaultResolver(): NamespaceGroupResolver
    {
        return new NamespaceGroupResolver([
            'controller' => ['~\\\\Controller\\\\~'],
            'service'    => ['~\\\\Services?\\\\~', '~\\\\Helpers?\\\\~'],
            'repository' => ['~\\\\Repository\\\\~'],
            'entity'     => ['~\\\\Entity\\\\~'],
            'command'    => ['~\\\\Command\\\\~'],
            'migration'  => ['~\\\\Migrations?\\\\~'],
            'project'    => ['~^App\\\\~', '~^PTGS\\\\~'],
            'dbAccess'   => [
                'Doctrine\\ORM\\EntityManagerInterface',
                'Doctrine\\DBAL\\Connection',
                'PDO',
            ],
        ]);
    }
}
