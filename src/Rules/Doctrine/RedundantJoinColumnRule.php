<?php

declare(strict_types=1);

namespace PTGS\PHPStanRules\Rules\Doctrine;

use PhpParser\Node;
use PhpParser\Node\Stmt\Property;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleError;
use PHPStan\Rules\RuleErrorBuilder;
use PTGS\PHPStanRules\Rules\LevelAwareRule;

/**
 * Flags #[ORM\JoinColumn(...)] attributes whose explicit values match Doctrine's defaults.
 *
 * Defaults checked (assuming UnderscoreNamingStrategy, as used in property-api and team-ops):
 *   - name                 → <snake_case_property>_id
 *   - referencedColumnName → 'id'
 *   - nullable             → true
 *   - unique               → false
 *   - onDelete             → null
 *   - columnDefinition     → null
 *   - options              → null or []
 *
 * If every argument is redundant (or the attribute has no arguments at all), the whole
 * #[ORM\JoinColumn] can be deleted — a single "entirely redundant" message is emitted
 * with identifier `ptgs.redundantJoinColumnAttribute`. Otherwise each redundant argument
 * is reported individually with identifier `ptgs.redundantJoinColumnArgument`.
 *
 * @implements Rule<Property>
 */
final class RedundantJoinColumnRule implements Rule
{
    use LevelAwareRule;

    private const int MIN_LEVEL = 6;

    public function __construct(
        private readonly ?int $ruleLevel = null,
    ) {}

    public function getNodeType(): string
    {
        return Property::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if ($this->belowMinLevel()) {
            return [];
        }

        $joinColumnAttr = $this->findJoinColumnAttribute($node);
        if (null === $joinColumnAttr) {
            return [];
        }

        $propertyName = $node->props[0]->name->name;
        $expectedName = $this->underscoreNumberAware($propertyName) . '_id';

        if ([] === $joinColumnAttr->args) {
            return [$this->buildEntirelyRedundantError($joinColumnAttr, $propertyName)];
        }

        $redundantArgs = [];
        $nonRedundantArgs = [];

        foreach ($joinColumnAttr->args as $arg) {
            $argName = $arg->name?->name;
            if (null === $argName) {
                $nonRedundantArgs[] = $arg;
                continue;
            }

            if ($this->isArgumentRedundant($argName, $arg->value, $expectedName)) {
                $redundantArgs[] = [$argName, $arg];
            } else {
                $nonRedundantArgs[] = $arg;
            }
        }

        if ([] === $redundantArgs) {
            return [];
        }

        if ([] === $nonRedundantArgs) {
            return [$this->buildEntirelyRedundantError($joinColumnAttr, $propertyName)];
        }

        $errors = [];
        foreach ($redundantArgs as [$argName, $arg]) {
            $errors[] = RuleErrorBuilder::message(\sprintf(
                'Redundant JoinColumn argument %s on property $%s — this matches the Doctrine default.',
                $argName,
                $propertyName,
            ))
                ->identifier('ptgs.redundantJoinColumnArgument')
                ->line($arg->getStartLine())
                ->build();
        }

        return $errors;
    }

    private function findJoinColumnAttribute(Property $node): ?Node\Attribute
    {
        foreach ($node->attrGroups as $attrGroup) {
            foreach ($attrGroup->attrs as $attr) {
                $name = $attr->name->toString();
                if ('JoinColumn' === $name
                    || 'Doctrine\ORM\Mapping\JoinColumn' === $name
                    || 'ORM\JoinColumn' === $name
                ) {
                    return $attr;
                }
            }
        }

        return null;
    }

    private function isArgumentRedundant(string $argName, Node\Expr $value, string $expectedName): bool
    {
        return match ($argName) {
            'name' => $this->isStringLiteral($value, $expectedName),
            'referencedColumnName' => $this->isStringLiteral($value, 'id'),
            'nullable' => $this->isBooleanLiteral($value, true),
            'unique' => $this->isBooleanLiteral($value, false),
            'onDelete', 'columnDefinition' => $this->isNullLiteral($value),
            'options' => $this->isNullLiteral($value) || $this->isEmptyArrayLiteral($value),
            default => false,
        };
    }

    private function isStringLiteral(Node\Expr $value, string $expected): bool
    {
        return $value instanceof Node\Scalar\String_ && $value->value === $expected;
    }

    private function isBooleanLiteral(Node\Expr $value, bool $expected): bool
    {
        if (!$value instanceof Node\Expr\ConstFetch) {
            return false;
        }

        $name = strtolower($value->name->toString());
        return ($expected && 'true' === $name) || (!$expected && 'false' === $name);
    }

    private function isNullLiteral(Node\Expr $value): bool
    {
        return $value instanceof Node\Expr\ConstFetch
            && 'null' === strtolower($value->name->toString());
    }

    private function isEmptyArrayLiteral(Node\Expr $value): bool
    {
        return $value instanceof Node\Expr\Array_ && [] === $value->items;
    }

    private function buildEntirelyRedundantError(Node\Attribute $attr, string $propertyName): RuleError
    {
        return RuleErrorBuilder::message(\sprintf(
            'Redundant JoinColumn on property $%s — every value matches the Doctrine default; the attribute can be removed.',
            $propertyName,
        ))
            ->identifier('ptgs.redundantJoinColumnAttribute')
            ->line($attr->getStartLine())
            ->build();
    }

    /**
     * Mirrors Doctrine\ORM\Mapping\UnderscoreNamingStrategy with numberAware = true,
     * which is the strategy configured in both property-api and team-ops.
     */
    private function underscoreNumberAware(string $literal): string
    {
        $value = preg_replace('/(?<=[a-z])([A-Z])/', '_$1', $literal) ?? $literal;
        $value = preg_replace('/(?<=[0-9])([A-Z])/', '_$1', $value) ?? $value;
        $value = preg_replace('/(?<=[a-z])([0-9])/', '_$1', $value) ?? $value;

        return strtolower($value);
    }
}
