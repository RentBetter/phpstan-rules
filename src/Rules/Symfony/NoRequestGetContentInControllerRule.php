<?php

declare(strict_types=1);

namespace RentBetter\PHPStanRules\Rules\Symfony;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\ObjectType;

/**
 * Controllers should not call Request::getContent() directly.
 * Use #[MapRequestPayload] with a typed DTO instead.
 *
 * @implements Rule<MethodCall>
 */
final class NoRequestGetContentInControllerRule implements Rule
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

        if ('getContent' !== $node->name->name) {
            return [];
        }

        $classReflection = $scope->getClassReflection();
        if (null === $classReflection) {
            return [];
        }

        if (!str_ends_with($classReflection->getName(), 'Controller')) {
            return [];
        }

        $callerType = $scope->getType($node->var);
        $requestType = new ObjectType('Symfony\Component\HttpFoundation\Request');

        if ($requestType->isSuperTypeOf($callerType)->yes()) {
            return [
                RuleErrorBuilder::message(
                    'Avoid calling Request::getContent() in controllers. Use #[MapRequestPayload] with a typed DTO instead.',
                )
                    ->identifier('rentbetter.noRequestGetContentInController')
                    ->build(),
            ];
        }

        return [];
    }
}
