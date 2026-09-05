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
 * Route methods should use descriptive ID names like $tenancyId / {tenancyId},
 * not a generic $id / {id}.
 *
 * The path is checked as well as the signature because a route can carry {id}
 * while the method binds it under another name — through #[MapEntity], a value
 * resolver, or simply by never taking the parameter at all.
 *
 * @implements Rule<ClassMethod>
 */
final class NoGenericIdParameterRule implements Rule
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

        $routeAttributes = RouteAttributeHelper::getRouteAttributes($node);
        if ([] === $routeAttributes) {
            return [];
        }

        foreach ($routeAttributes as $attribute) {
            $path = RouteAttributeHelper::getRoutePath($attribute);
            if (null !== $path && str_contains($path, '{id}')) {
                return [
                    RuleErrorBuilder::message(
                        'Route path parameter should use a descriptive name like {tenancyId} instead of {id}.',
                    )
                        ->identifier('ptgs.noGenericId')
                        ->line($attribute->getStartLine())
                        ->build(),
                ];
            }
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
                        ->identifier('ptgs.noGenericId')
                        ->line($param->getStartLine())
                        ->build(),
                ];
            }
        }

        return [];
    }
}
