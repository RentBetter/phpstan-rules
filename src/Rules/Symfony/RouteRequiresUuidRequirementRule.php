<?php

declare(strict_types=1);

namespace RentBetter\PHPStanRules\Rules\Symfony;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Route path parameters ending in "Id" (e.g. {tenancyId}) must have
 * a requirements: constraint.
 *
 * @implements Rule<ClassMethod>
 */
final class RouteRequiresUuidRequirementRule implements Rule
{
    public function getNodeType(): string
    {
        return ClassMethod::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        $errors = [];

        foreach (RouteAttributeHelper::getRouteAttributes($node) as $attr) {
            $path = RouteAttributeHelper::getRoutePath($attr);
            if (null === $path) {
                continue;
            }

            // Find all {fooId} parameters in the path
            if (!preg_match_all('/\{(\w*Id)\}/', $path, $matches)) {
                continue;
            }

            $requirements = $this->getRequirementKeys($attr);

            foreach ($matches[1] as $paramName) {
                if (!\in_array($paramName, $requirements, true)) {
                    $errors[] = RuleErrorBuilder::message(\sprintf(
                        'Route parameter {%s} should have a requirements: constraint (e.g. Uuid::REGEX).',
                        $paramName,
                    ))
                        ->identifier('rentbetter.routeRequiresUuidRequirement')
                        ->line($attr->getStartLine())
                        ->build();
                }
            }
        }

        return $errors;
    }

    /**
     * @return list<string>
     */
    private function getRequirementKeys(Node\Attribute $attr): array
    {
        $reqExpr = RouteAttributeHelper::getNamedArgValue($attr, 'requirements');
        if (!$reqExpr instanceof Node\Expr\Array_) {
            return [];
        }

        $keys = [];
        foreach ($reqExpr->items as $item) {
            if ($item->key instanceof Node\Scalar\String_) {
                $keys[] = $item->key->value;
            }
        }

        return $keys;
    }
}
