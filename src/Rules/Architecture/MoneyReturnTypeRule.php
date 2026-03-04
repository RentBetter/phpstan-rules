<?php

declare(strict_types=1);

namespace RentBetter\PHPStanRules\Rules\Architecture;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Public methods should return MoneyInterface, not MoneyModelV2.
 * Callers needing MoneyModelV2 should use MoneyModelV2::of($money).
 *
 * @implements Rule<ClassMethod>
 */
final class MoneyReturnTypeRule implements Rule
{
    public function getNodeType(): string
    {
        return ClassMethod::class;
    }

    /** @return list<IdentifierRuleError> */
    public function processNode(Node $node, Scope $scope): array
    {
        if (!$node->isPublic()) {
            return [];
        }

        $returnType = $node->returnType;
        if (null === $returnType) {
            return [];
        }

        if ($this->isMoneyModelV2($returnType)) {
            return [
                RuleErrorBuilder::message(
                    'Public methods should return MoneyInterface, not MoneyModelV2.',
                )
                    ->identifier('rentbetter.moneyReturnType')
                    ->build(),
            ];
        }

        // Also check nullable ?MoneyModelV2
        if ($returnType instanceof Node\NullableType && $this->isMoneyModelV2($returnType->type)) {
            return [
                RuleErrorBuilder::message(
                    'Public methods should return ?MoneyInterface, not ?MoneyModelV2.',
                )
                    ->identifier('rentbetter.moneyReturnType')
                    ->build(),
            ];
        }

        return [];
    }

    private function isMoneyModelV2(Node $type): bool
    {
        if ($type instanceof Node\Name) {
            $name = $type->toString();

            return 'MoneyModelV2' === $name || str_ends_with($name, '\MoneyModelV2');
        }

        return false;
    }
}
