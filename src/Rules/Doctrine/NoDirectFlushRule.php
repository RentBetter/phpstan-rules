<?php

declare(strict_types=1);

namespace RentBetter\PHPStanRules\Rules\Doctrine;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\ObjectType;

/**
 * Direct calls to EntityManagerInterface::flush() are discouraged.
 * Use a service method with a $save parameter instead.
 *
 * @implements Rule<MethodCall>
 */
final class NoDirectFlushRule implements Rule
{
    public function getNodeType(): string
    {
        return MethodCall::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if (!$node->name instanceof Node\Identifier) {
            return [];
        }

        if ('flush' !== $node->name->name) {
            return [];
        }

        $callerType = $scope->getType($node->var);
        $emType = new ObjectType('Doctrine\ORM\EntityManagerInterface');

        if ($emType->isSuperTypeOf($callerType)->yes()) {
            return [
                RuleErrorBuilder::message(
                    'Avoid calling EntityManagerInterface::flush() directly. Use a service with a $save parameter instead.',
                )
                    ->identifier('rentbetter.noDirectFlush')
                    ->build(),
            ];
        }

        return [];
    }
}
