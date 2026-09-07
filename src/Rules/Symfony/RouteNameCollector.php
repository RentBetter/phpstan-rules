<?php

declare(strict_types=1);

namespace PTGS\PHPStanRules\Rules\Symfony;

use PhpParser\Node;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;
use PHPStan\Collectors\Collector;

/**
 * Collects every explicit #[Route(name: '...')] declaration so UniqueRouteNameRule
 * can detect global collisions. Symfony route names are a single flat namespace and
 * a later-registered duplicate silently replaces the earlier route — the shadowed
 * path 404s with no error anywhere.
 *
 * @implements Collector<ClassMethod, list<array{string, int}>>
 */
final class RouteNameCollector implements Collector
{
    public function getNodeType(): string
    {
        return ClassMethod::class;
    }

    public function processNode(Node $node, Scope $scope): ?array
    {
        $names = [];
        foreach (RouteAttributeHelper::getRouteAttributes($node) as $attribute) {
            foreach ($attribute->args as $arg) {
                if ('name' === $arg->name?->toString() && $arg->value instanceof String_) {
                    $names[] = [$arg->value->value, $arg->getStartLine()];
                }
            }
        }

        return [] === $names ? null : $names;
    }
}
