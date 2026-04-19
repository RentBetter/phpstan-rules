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
 * Route path segments must use camelCase, not snake_case.
 *
 * Good: /paymentAccounts/{accountId}/acceptRate
 * Bad:  /payment_accounts/{accountId}/accept_rate
 *
 * @implements Rule<ClassMethod>
 */
final class RoutePathCamelCaseRule implements Rule
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

        $errors = [];

        foreach ($routeAttrs as $attr) {
            $path = RouteAttributeHelper::getRoutePath($attr);
            if (null === $path) {
                continue;
            }

            foreach (explode('/', $path) as $segment) {
                if ('' === $segment || str_starts_with($segment, '{')) {
                    continue;
                }

                if (str_contains($segment, '_')) {
                    $errors[] = RuleErrorBuilder::message(
                        \sprintf('Route path segment "%s" uses snake_case. Use camelCase instead.', $segment),
                    )
                        ->identifier('ptgs.routePathCamelCase')
                        ->build();
                    break; // One error per route is enough
                }
            }
        }

        return $errors;
    }
}
