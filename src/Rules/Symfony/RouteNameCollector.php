<?php

declare(strict_types=1);

namespace PTGS\PHPStanRules\Rules\Symfony;

use PhpParser\Node;
use PhpParser\Node\Attribute;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use PHPStan\Collectors\Collector;

/**
 * Collects the name each explicit #[Route(name: '...')] registers so UniqueRouteNameRule
 * can detect global collisions. Symfony route names are a single flat namespace and
 * a later-registered duplicate silently replaces the earlier route — the shadowed
 * path 404s with no error anywhere.
 *
 * Names resolve the way Symfony's AttributeClassLoader resolves them: the first
 * class-level #[Route] prefixes its name onto every method route in the class, and
 * on an invokable class with no method routes the class-level attributes are the
 * routes themselves, unprefixed.
 *
 * @implements Collector<Class_, list<array{string, int}>>
 */
final class RouteNameCollector implements Collector
{
    public function getNodeType(): string
    {
        return Class_::class;
    }

    public function processNode(Node $node, Scope $scope): ?array
    {
        $classAttributes = RouteAttributeHelper::getRouteAttributes($node);
        $prefix = [] === $classAttributes
            ? ''
            : (RouteAttributeHelper::getNamedArgStringValue($classAttributes[0], 'name') ?? '');

        $names = [];
        $hasMethodRoutes = false;
        foreach ($node->getMethods() as $method) {
            foreach (RouteAttributeHelper::getRouteAttributes($method) as $attribute) {
                $hasMethodRoutes = true;
                $explicit = self::explicitName($attribute);
                if (null !== $explicit) {
                    [$name, $line] = $explicit;
                    $names[] = [$prefix . $name, $line];
                }
            }
        }

        if (!$hasMethodRoutes && null !== $node->getMethod('__invoke')) {
            foreach ($classAttributes as $attribute) {
                $explicit = self::explicitName($attribute);
                if (null !== $explicit) {
                    $names[] = $explicit;
                }
            }
        }

        return [] === $names ? null : $names;
    }

    /**
     * The route's explicit `name:` and the line it is declared on, or null when the
     * name is left to Symfony's default naming.
     *
     * @return array{string, int}|null
     */
    private static function explicitName(Attribute $attribute): ?array
    {
        foreach ($attribute->args as $arg) {
            if ('name' === $arg->name?->toString() && $arg->value instanceof String_) {
                return [$arg->value->value, $arg->getStartLine()];
            }
        }

        return null;
    }
}
