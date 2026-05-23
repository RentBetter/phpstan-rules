<?php

declare(strict_types=1);

namespace PTGS\PHPStanRules\Rules\Symfony;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\ObjectType;
use PTGS\PHPStanRules\Rules\LevelAwareRule;
use PTGS\PHPStanRules\Rules\NamespaceGroupResolver;

/**
 * Controllers should not call Request::getContent() directly.
 * Use the Form component to bind the request into a typed DTO instead.
 *
 * @implements Rule<MethodCall>
 */
final class NoRequestGetContentInControllerRule implements Rule
{
    use LevelAwareRule;

    private const int MIN_LEVEL = 5;

    public function __construct(
        private readonly NamespaceGroupResolver $resolver,
        private readonly ?int $ruleLevel = null,
    ) {}

    public function getNodeType(): string
    {
        return MethodCall::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if ($this->belowMinLevel()) {
            return [];
        }

        if (!$node->name instanceof Node\Identifier) {
            return [];
        }

        if ('getContent' !== $node->name->name) {
            return [];
        }

        $classReflection = $scope->getClassReflection();
        if (null === $classReflection || !$this->resolver->inGroup($classReflection->getName(), 'controller')) {
            return [];
        }

        $callerType = $scope->getType($node->var);
        $requestType = new ObjectType('Symfony\Component\HttpFoundation\Request');

        if ($requestType->isSuperTypeOf($callerType)->yes()) {
            return [
                RuleErrorBuilder::message(
                    'Avoid calling Request::getContent() in controllers. Use the Form component to bind the request into a typed DTO instead.',
                )
                    ->identifier('ptgs.noRequestGetContentInController')
                    ->build(),
            ];
        }

        return [];
    }
}
