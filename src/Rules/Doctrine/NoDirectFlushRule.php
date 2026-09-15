<?php

declare(strict_types=1);

namespace PTGS\PHPStanRules\Rules\Doctrine;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\ObjectType;
use PTGS\PHPStanRules\Rules\LevelAwareRule;
use PTGS\PHPStanRules\Rules\NamespaceGroupResolver;

/**
 * Direct calls to EntityManagerInterface::flush() are discouraged.
 * Use a service method with a $save parameter instead.
 *
 * Repositories are exempt: they are the layer that owns persistence, so the
 * `save($entity, flush: $save)` a service delegates to is exactly where the one
 * real flush() call belongs.
 *
 * @implements Rule<MethodCall>
 */
final class NoDirectFlushRule implements Rule
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

        if ('flush' !== $node->name->name) {
            return [];
        }

        $callerType = $scope->getType($node->var);
        $emType = new ObjectType('Doctrine\ORM\EntityManagerInterface');

        if (!$emType->isSuperTypeOf($callerType)->yes()) {
            return [];
        }

        // A trait is analysed once per using class, and the scope's class is that user —
        // so a repository base trait is judged as the repository it is part of.
        $className = $scope->getClassReflection()?->getName();
        if (null !== $className && $this->resolver->inGroup($className, 'repository')) {
            return [];
        }

        return [
            RuleErrorBuilder::message(
                'Avoid calling EntityManagerInterface::flush() directly. Use a service with a $save parameter instead.',
            )
                ->identifier('ptgs.noDirectFlush')
                ->build(),
        ];
    }
}
