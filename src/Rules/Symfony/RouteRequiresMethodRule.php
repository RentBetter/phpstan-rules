<?php

declare(strict_types=1);

namespace RentBetter\PHPStanRules\Rules\Symfony;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Every #[Route] attribute must specify the HTTP methods: parameter.
 *
 * @implements Rule<ClassMethod>
 */
final class RouteRequiresMethodRule implements Rule
{
    public function getNodeType(): string
    {
        return ClassMethod::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        $errors = [];

        foreach (RouteAttributeHelper::getRouteAttributes($node) as $attr) {
            $methods = RouteAttributeHelper::getNamedArgValue($attr, 'methods');
            if (null === $methods) {
                $errors[] = RuleErrorBuilder::message(\sprintf(
                    'Route on %s() is missing the methods: parameter. Specify methods: \'GET\' (or POST, PUT, DELETE).',
                    $node->name->name,
                ))
                    ->identifier('rentbetter.routeRequiresMethod')
                    ->line($attr->getStartLine())
                    ->build();
            }
        }

        return $errors;
    }
}
