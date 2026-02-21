<?php

declare(strict_types=1);

namespace RentBetter\PHPStanRules\Rules\Symfony;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * The Route name: parameter should match the method name minus the "Action" suffix.
 * e.g. name: 'getTenancy' → getTenancyAction()
 *
 * @implements Rule<ClassMethod>
 */
final class RouteNameMatchesMethodRule implements Rule
{
    public function getNodeType(): string
    {
        return ClassMethod::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        $errors = [];

        foreach (RouteAttributeHelper::getRouteAttributes($node) as $attr) {
            $routeName = RouteAttributeHelper::getNamedArgStringValue($attr, 'name');
            if (null === $routeName) {
                continue;
            }

            $methodName = $node->name->name;
            $expectedRouteName = str_ends_with($methodName, 'Action')
                ? substr($methodName, 0, -6)
                : $methodName;

            if ($routeName !== $expectedRouteName) {
                $errors[] = RuleErrorBuilder::message(\sprintf(
                    'Route name \'%s\' does not match method name. Expected \'%s\' (from %s()).',
                    $routeName,
                    $expectedRouteName,
                    $methodName,
                ))
                    ->identifier('rentbetter.routeNameMatchesMethod')
                    ->line($attr->getStartLine())
                    ->build();
            }
        }

        return $errors;
    }
}
