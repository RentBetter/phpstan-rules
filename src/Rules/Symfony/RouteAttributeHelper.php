<?php

declare(strict_types=1);

namespace RentBetter\PHPStanRules\Rules\Symfony;

use PhpParser\Node;
use PhpParser\Node\Attribute;
use PhpParser\Node\Stmt\ClassMethod;

/**
 * Shared helper for extracting Symfony #[Route] attributes from class methods.
 */
final class RouteAttributeHelper
{
    /**
     * @return list<Attribute>
     */
    public static function getRouteAttributes(ClassMethod $method): array
    {
        $attributes = [];
        foreach ($method->attrGroups as $attrGroup) {
            foreach ($attrGroup->attrs as $attr) {
                $name = $attr->name->toString();
                if ('Route' === $name
                    || 'Symfony\Component\Routing\Annotation\Route' === $name
                    || 'Symfony\Component\Routing\Attribute\Route' === $name
                ) {
                    $attributes[] = $attr;
                }
            }
        }

        return $attributes;
    }

    public static function getNamedArgValue(Attribute $attr, string $name): ?Node\Expr
    {
        foreach ($attr->args as $arg) {
            if ($arg->name?->name === $name) {
                return $arg->value;
            }
        }

        return null;
    }

    public static function getNamedArgStringValue(Attribute $attr, string $name): ?string
    {
        $value = self::getNamedArgValue($attr, $name);
        if ($value instanceof Node\Scalar\String_) {
            return $value->value;
        }

        return null;
    }

    /**
     * Get the route path — first positional arg or named 'path' arg.
     */
    public static function getRoutePath(Attribute $attr): ?string
    {
        // Check named 'path' argument
        $path = self::getNamedArgStringValue($attr, 'path');
        if (null !== $path) {
            return $path;
        }

        // Check first positional argument
        if (isset($attr->args[0]) && null === $attr->args[0]->name) {
            $value = $attr->args[0]->value;
            if ($value instanceof Node\Scalar\String_) {
                return $value->value;
            }
        }

        return null;
    }
}
