<?php

declare(strict_types=1);

namespace PTGS\PHPStanRules\Rules\Enum;

use PhpParser\Node;
use PhpParser\Node\Stmt\Property;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PTGS\PHPStanRules\Rules\LevelAwareRule;

/**
 * Doctrine #[Column] attributes on properties containing "status" in their name
 * must specify an enumType: parameter.
 *
 * @implements Rule<Property>
 */
final class StatusColumnMustBeEnumRule implements Rule
{
    use LevelAwareRule;

    private const int MIN_LEVEL = 6;

    private const array INTEGER_COLUMN_TYPES = ['smallint', 'integer', 'bigint'];

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

        $errors = [];

        foreach ($node->props as $prop) {
            $propName = $prop->name->name;

            if (!$this->isStatusProperty($propName)) {
                continue;
            }

            $columnAttr = $this->getColumnAttribute($node);
            if (null === $columnAttr) {
                continue;
            }

            // An integer status is a protocol code — the HTTP status on a request log, say —
            // not a state machine of our own, and no enum stands behind it.
            if ($this->isIntegerColumn($node, $columnAttr)) {
                continue;
            }

            if (!$this->hasEnumType($columnAttr)) {
                $errors[] = RuleErrorBuilder::message(\sprintf(
                    'Status property $%s has a #[Column] without enumType:. Use #[Column(enumType: MyStatusEnum::class)].',
                    $propName,
                ))
                    ->identifier('ptgs.statusColumnMustBeEnum')
                    ->line($columnAttr->getStartLine())
                    ->build();
            }
        }

        return $errors;
    }

    private function isStatusProperty(string $name): bool
    {
        // Ends with, not contains: $statusChangedAt / $statusUpdatedBy are metadata *about* a
        // status column, not status columns themselves, and have no business being enum-backed.
        // Every real one reads as a noun ending in the word — $status, $paymentStatus,
        // $fulfilmentStatus.
        return str_ends_with(strtolower($name), 'status');
    }

    private function getColumnAttribute(Property $property): ?Node\Attribute
    {
        foreach ($property->attrGroups as $attrGroup) {
            foreach ($attrGroup->attrs as $attr) {
                $name = $attr->name->toString();
                if ('Column' === $name
                    || 'ORM\Column' === $name
                    || 'Doctrine\ORM\Mapping\Column' === $name
                ) {
                    return $attr;
                }
            }
        }

        return null;
    }

    private function isIntegerColumn(Property $property, Node\Attribute $attr): bool
    {
        $type = $property->type instanceof Node\NullableType ? $property->type->type : $property->type;
        if ($type instanceof Node\Identifier && 'int' === $type->toLowerString()) {
            return true;
        }

        foreach ($attr->args as $arg) {
            if ('type' !== $arg->name?->name) {
                continue;
            }
            // type: 'integer' or type: Types::INTEGER — the constant's name is its value upcased.
            if ($arg->value instanceof Node\Scalar\String_) {
                return \in_array(strtolower($arg->value->value), self::INTEGER_COLUMN_TYPES, true);
            }
            if ($arg->value instanceof Node\Expr\ClassConstFetch && $arg->value->name instanceof Node\Identifier) {
                return \in_array(strtolower($arg->value->name->name), self::INTEGER_COLUMN_TYPES, true);
            }
        }

        return false;
    }

    private function hasEnumType(Node\Attribute $attr): bool
    {
        foreach ($attr->args as $arg) {
            if ('enumType' === $arg->name?->name) {
                return true;
            }
        }

        return false;
    }
}
