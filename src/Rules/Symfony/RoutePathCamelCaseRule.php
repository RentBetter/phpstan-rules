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
 * Route path segments must use camelCase — not snake_case or kebab-case.
 *
 * Good: /paymentAccounts/{accountId}/acceptRate
 * Bad:  /payment_accounts/{accountId}/accept_rate
 * Bad:  /payment-accounts/{accountId}/accept-rate
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
                if ('' === $segment) {
                    continue;
                }

                // Judge only the literal text. Placeholders are Symfony's namespace, not ours:
                // {_format} and {_locale} are reserved content-negotiation params whose leading
                // underscore is mandatory, and a segment like "reports.{_format}" is not a
                // snake_case word choice. Stripping them also covers a bare "{param}" segment.
                $literal = preg_replace('/\{[^}]*\}/', '', $segment) ?? $segment;
                if ('' === $literal) {
                    continue;
                }

                $style = match (true) {
                    str_contains($literal, '_') => 'snake_case',
                    str_contains($literal, '-') => 'kebab-case',
                    default => null,
                };

                if (null !== $style) {
                    $errors[] = RuleErrorBuilder::message(
                        \sprintf('Route path segment "%s" uses %s. Use camelCase instead.', $segment, $style),
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
