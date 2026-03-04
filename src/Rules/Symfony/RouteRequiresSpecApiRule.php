<?php

declare(strict_types=1);

namespace RentBetter\PHPStanRules\Rules\Symfony;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Route methods must have a #[Spec\Api] attribute for API documentation.
 *
 * @implements Rule<ClassMethod>
 */
final class RouteRequiresSpecApiRule implements Rule
{
    private const SPEC_API_NAMES = [
        'Api',
        'Spec\Api',
        'Spec\\Api',
        'PTGS\PropertyApi\_\Spec\Api',
        'PTGS\\PropertyApi\\_\\Spec\\Api',
    ];

    public function getNodeType(): string
    {
        return ClassMethod::class;
    }

    /** @return list<IdentifierRuleError> */
    public function processNode(Node $node, Scope $scope): array
    {
        if ([] === RouteAttributeHelper::getRouteAttributes($node)) {
            return [];
        }

        foreach ($node->attrGroups as $attrGroup) {
            foreach ($attrGroup->attrs as $attr) {
                $name = $attr->name->toString();
                if (\in_array($name, self::SPEC_API_NAMES, true)) {
                    return [];
                }
            }
        }

        return [
            RuleErrorBuilder::message(
                'Route method is missing #[Spec\Api] attribute for API documentation.',
            )
                ->identifier('rentbetter.routeRequiresSpecApi')
                ->build(),
        ];
    }
}
