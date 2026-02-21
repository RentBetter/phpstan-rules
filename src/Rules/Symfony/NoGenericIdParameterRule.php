<?php

declare(strict_types=1);

namespace RentBetter\PHPStanRules\Rules\Symfony;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Route methods should use descriptive ID parameter names like $tenancyId,
 * not generic $id.
 *
 * @implements Rule<ClassMethod>
 */
final class NoGenericIdParameterRule implements Rule
{
    public function getNodeType(): string
    {
        return ClassMethod::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if ([] === RouteAttributeHelper::getRouteAttributes($node)) {
            return [];
        }

        foreach ($node->params as $param) {
            $paramName = $param->var instanceof Node\Expr\Variable && \is_string($param->var->name)
                ? $param->var->name
                : null;

            if ('id' === $paramName) {
                return [
                    RuleErrorBuilder::message(
                        'Route parameter should use a descriptive name like $tenancyId instead of $id.',
                    )
                        ->identifier('rentbetter.noGenericId')
                        ->line($param->getStartLine())
                        ->build(),
                ];
            }
        }

        return [];
    }
}
