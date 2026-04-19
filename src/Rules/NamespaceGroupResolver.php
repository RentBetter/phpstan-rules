<?php

declare(strict_types=1);

namespace RentBetter\PHPStanRules\Rules;

use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;

/**
 * Resolves whether a class belongs to a named "group" — controller, service,
 * entity, dbAccess, etc. — based on a configurable map of patterns.
 *
 * Group entries are either:
 *   - A regex with `~` delimiters (e.g. `~\\Service\\~`) — string-matched against the FQCN
 *   - A literal FQCN (e.g. `Doctrine\DBAL\Connection`) — exact match for `inGroup()`,
 *     subtype-aware match for `inGroupByType()` (so subclasses/implementations are caught)
 */
final readonly class NamespaceGroupResolver
{
    /**
     * @param array<string, list<string>> $groups
     */
    public function __construct(
        private array $groups,
    ) {}

    public function inGroup(string $fqcn, string $groupName): bool
    {
        foreach ($this->patternsFor($groupName) as $pattern) {
            if ($this->isRegex($pattern)) {
                if (1 === preg_match($pattern, $fqcn)) {
                    return true;
                }
            } elseif ($pattern === $fqcn) {
                return true;
            }
        }

        return false;
    }

    /**
     * Type-aware match. Literal-FQCN entries match by subtype (so a class that
     * implements `EntityManagerInterface` matches the `dbAccess` group). Regex
     * entries match against any of the type's resolved class names.
     */
    public function inGroupByType(Type $type, string $groupName): bool
    {
        $classNames = $type->getObjectClassNames();

        foreach ($this->patternsFor($groupName) as $pattern) {
            if ($this->isRegex($pattern)) {
                foreach ($classNames as $name) {
                    if (1 === preg_match($pattern, $name)) {
                        return true;
                    }
                }
            } elseif ((new ObjectType($pattern))->isSuperTypeOf($type)->yes()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return list<string>
     */
    public function getGroups(string $fqcn): array
    {
        $matches = [];
        foreach (array_keys($this->groups) as $name) {
            if ($this->inGroup($fqcn, $name)) {
                $matches[] = $name;
            }
        }

        return $matches;
    }

    /**
     * @return list<string>
     */
    private function patternsFor(string $groupName): array
    {
        if (!isset($this->groups[$groupName])) {
            throw new \InvalidArgumentException(\sprintf(
                'Unknown namespace group "%s". Defined groups: %s',
                $groupName,
                '' === ($defined = implode(', ', array_keys($this->groups))) ? '(none)' : $defined,
            ));
        }

        return $this->groups[$groupName];
    }

    private function isRegex(string $pattern): bool
    {
        return strlen($pattern) >= 2
            && '~' === $pattern[0]
            && '~' === $pattern[strlen($pattern) - 1];
    }
}
