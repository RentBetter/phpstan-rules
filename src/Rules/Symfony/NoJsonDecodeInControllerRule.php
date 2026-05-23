<?php

declare(strict_types=1);

namespace PTGS\PHPStanRules\Rules\Symfony;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PTGS\PHPStanRules\Rules\LevelAwareRule;
use PTGS\PHPStanRules\Rules\NamespaceGroupResolver;

/**
 * Controllers must not hand-parse JSON. Use the Form component to bind the
 * request into a typed DTO instead.
 *
 * Bad:  $data = json_decode($request->getContent(), true);
 * Good: $form = $this->createForm(FooType::class);
 *       $form->handleRequest($request);
 *       if ($form->isValid()) { ... $form->getData() ... }
 *
 * @implements Rule<FuncCall>
 */
final class NoJsonDecodeInControllerRule implements Rule
{
    use LevelAwareRule;

    private const int MIN_LEVEL = 5;

    public function __construct(
        private readonly NamespaceGroupResolver $resolver,
        private readonly ?int $ruleLevel = null,
    ) {}

    public function getNodeType(): string
    {
        return FuncCall::class;
    }

    /** @return list<IdentifierRuleError> */
    public function processNode(Node $node, Scope $scope): array
    {
        if ($this->belowMinLevel()) {
            return [];
        }

        if (!$node->name instanceof Node\Name) {
            return [];
        }

        if ('json_decode' !== $node->name->toLowerString()) {
            return [];
        }

        $classReflection = $scope->getClassReflection();
        if (null === $classReflection || !$this->resolver->inGroup($classReflection->getName(), 'controller')) {
            return [];
        }

        return [
            RuleErrorBuilder::message(
                'Avoid calling json_decode() in controllers. Use the Form component to bind the request into a typed DTO instead.',
            )
                ->identifier('ptgs.noJsonDecodeInController')
                ->build(),
        ];
    }
}
