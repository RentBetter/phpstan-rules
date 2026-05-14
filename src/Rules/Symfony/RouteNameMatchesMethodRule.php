<?php

declare(strict_types=1);

namespace PTGS\PHPStanRules\Rules\Symfony;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PTGS\PHPStanRules\Rules\LevelAwareRule;

/**
 * The Route name: parameter must end in the method name minus the "Action" suffix,
 * optionally preceded by one or more `prefix:` segments matching the documented
 * pattern `{prefix}:{domain}:{subdomain}:{method}`.
 *
 * Examples (all OK for getThingAction()):
 *   name: 'getThing'
 *   name: 'admin:getThing'
 *   name: 'admin:things:getThing'
 *
 * @implements Rule<ClassMethod>
 */
final class RouteNameMatchesMethodRule implements Rule
{
    use LevelAwareRule;

    private const int MIN_LEVEL = 8;

    public function __construct(
        private readonly ?int $ruleLevel = null,
    ) {}

    public function getNodeType(): string
    {
        return ClassMethod::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if ($this->belowMinLevel()) {
            return [];
        }

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

            $lastSegment = false !== ($colonAt = strrpos($routeName, ':'))
                ? substr($routeName, $colonAt + 1)
                : $routeName;

            if ($lastSegment !== $expectedRouteName) {
                $errors[] = RuleErrorBuilder::message(\sprintf(
                    'Route name \'%s\' does not match method name. Expected \'%s\' or \'<prefix>:%s\' (from %s()).',
                    $routeName,
                    $expectedRouteName,
                    $expectedRouteName,
                    $methodName,
                ))
                    ->identifier('ptgs.routeNameMatchesMethod')
                    ->line($attr->getStartLine())
                    ->build();
            }
        }

        return $errors;
    }
}
