<?php

declare(strict_types=1);

namespace RentBetter\PHPStanRules\Rules\Enum;

use PhpParser\Node;
use PhpParser\Node\Stmt\Property;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Doctrine #[Column] attributes on properties containing "status" in their name
 * must specify an enumType: parameter.
 *
 * @implements Rule<Property>
 */
final class StatusColumnMustBeEnumRule implements Rule
{
    public function getNodeType(): string
    {
        return Property::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
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

            if (!$this->hasEnumType($columnAttr)) {
                $errors[] = RuleErrorBuilder::message(\sprintf(
                    'Status property $%s has a #[Column] without enumType:. Use #[Column(enumType: MyStatusEnum::class)].',
                    $propName,
                ))
                    ->identifier('rentbetter.statusColumnMustBeEnum')
                    ->line($columnAttr->getStartLine())
                    ->build();
            }
        }

        return $errors;
    }

    private function isStatusProperty(string $name): bool
    {
        return str_contains(strtolower($name), 'status');
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
