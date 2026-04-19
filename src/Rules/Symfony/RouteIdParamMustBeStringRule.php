<?php

declare(strict_types=1);

namespace PTGS\PHPStanRules\Rules\Symfony;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PTGS\PHPStanRules\Rules\LevelAwareRule;

/**
 * Route parameters ending in "Id" must be typed as string in the method signature,
 * not as entity objects.
 *
 * Good: public function getAction(ApiRequest $r, string $tenancyId): ApiResponse
 * Bad:  public function getAction(ApiRequest $r, Tenancy $tenancy): ApiResponse
 *
 * @implements Rule<ClassMethod>
 */
final class RouteIdParamMustBeStringRule implements Rule
{
    use LevelAwareRule;

    private const int MIN_LEVEL = 6;

    public function __construct(
        private readonly ?int $ruleLevel = null,
    ) {}

    public function getNodeType(): string
    {
        return ClassMethod::class;
    }

    /** @return list<IdentifierRuleError> */
    public function processNode(Node $node, Scope $scope): array
    {
        if ($this->belowMinLevel()) {
            return [];
        }

        $routeAttrs = RouteAttributeHelper::getRouteAttributes($node);
        if ([] === $routeAttrs) {
            return [];
        }

        // Collect {*Id} params from route paths
        $idParams = [];
        foreach ($routeAttrs as $attr) {
            $path = RouteAttributeHelper::getRoutePath($attr);
            if (null === $path) {
                continue;
            }
            preg_match_all('/\{(\w*Id)\}/', $path, $matches);
            foreach ($matches[1] as $param) {
                $idParams[$param] = true;
            }
        }

        if ([] === $idParams) {
            return [];
        }

        $errors = [];

        foreach ($node->params as $param) {
            if (!$param->var instanceof Node\Expr\Variable || !\is_string($param->var->name)) {
                continue;
            }

            $paramName = $param->var->name;
            if (!isset($idParams[$paramName])) {
                continue;
            }

            if (null === $param->type) {
                $errors[] = RuleErrorBuilder::message(
                    \sprintf('Route ID parameter $%s must be typed as string.', $paramName),
                )
                    ->identifier('ptgs.routeIdParamMustBeString')
                    ->build();
            } elseif ($param->type instanceof Node\Identifier) {
                if ('string' !== $param->type->name) {
                    $errors[] = RuleErrorBuilder::message(
                        \sprintf('Route ID parameter $%s must be typed as string, got %s.', $paramName, $param->type->name),
                    )
                        ->identifier('ptgs.routeIdParamMustBeString')
                        ->build();
                }
            } elseif ($param->type instanceof Node\Name) {
                $errors[] = RuleErrorBuilder::message(
                    \sprintf('Route ID parameter $%s must be typed as string, got %s.', $paramName, $param->type->toString()),
                )
                    ->identifier('ptgs.routeIdParamMustBeString')
                    ->build();
            }
        }

        return $errors;
    }
}
