<?php

declare(strict_types=1);

namespace RentBetter\PHPStanRules\Rules\Symfony;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use RentBetter\PHPStanRules\Rules\LevelAwareRule;

/**
 * Route methods must accept ApiRequest as first parameter and return ApiResponse.
 *
 * @implements Rule<ClassMethod>
 */
final class RouteMethodSignatureRule implements Rule
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

        if ([] === RouteAttributeHelper::getRouteAttributes($node)) {
            return [];
        }

        $errors = [];

        // Check first param is ApiRequest
        $params = $node->params;
        if ([] === $params) {
            $errors[] = RuleErrorBuilder::message(
                'Route method must have ApiRequest as first parameter.',
            )
                ->identifier('rentbetter.routeMethodSignature')
                ->build();
        } elseif (!$this->isNamedType($params[0]->type, 'ApiRequest')) {
            $errors[] = RuleErrorBuilder::message(
                'Route method first parameter must be typed as ApiRequest.',
            )
                ->identifier('rentbetter.routeMethodSignature')
                ->build();
        }

        // Check return type is ApiResponse
        if (null === $node->returnType) {
            $errors[] = RuleErrorBuilder::message(
                'Route method must declare ApiResponse return type.',
            )
                ->identifier('rentbetter.routeMethodSignature')
                ->build();
        } elseif (!$this->isResponseType($node->returnType)) {
            $errors[] = RuleErrorBuilder::message(
                'Route method must return ApiResponse.',
            )
                ->identifier('rentbetter.routeMethodSignature')
                ->build();
        }

        return $errors;
    }

    private function isNamedType(?Node $type, string $shortName): bool
    {
        if ($type instanceof Node\Name) {
            $name = $type->toString();

            return $shortName === $name || str_ends_with($name, '\\' . $shortName);
        }

        return false;
    }

    private function isResponseType(?Node $type): bool
    {
        if ($type instanceof Node\Name) {
            $name = $type->toString();

            return 'ApiResponse' === $name
                || str_ends_with($name, '\ApiResponse')
                || 'ApiStreamedResponse' === $name
                || str_ends_with($name, '\ApiStreamedResponse');
        }

        if ($type instanceof Node\UnionType) {
            foreach ($type->types as $t) {
                if ($this->isResponseType($t)) {
                    return true;
                }
            }
        }

        return false;
    }
}
