<?php

declare(strict_types=1);

namespace RentBetter\PHPStanRules\Rules\Symfony;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use RentBetter\PHPStanRules\Rules\LevelAwareRule;
use RentBetter\PHPStanRules\Rules\NamespaceGroupResolver;

/**
 * Controller route methods accessing more than one request parameter directly
 * should use a form type or DTO instead.
 *
 * Bad:  $page = $apiRequest->query->int('page');
 *       $sort = $apiRequest->query->str('sort');
 *
 * Good: $data = $this->processForm($apiRequest, ListFilterType::class);
 *
 * @implements Rule<ClassMethod>
 */
final class NoMultipleRequestParamsInControllerRule implements Rule
{
    use LevelAwareRule;

    private const int MIN_LEVEL = 5;

    public function __construct(
        private readonly NamespaceGroupResolver $resolver,
        private readonly ?int $ruleLevel = null,
    ) {}

    /** Properties on ApiRequest/Request that are param bags */
    private const GETTER_PROPERTIES = ['query', 'request'];

    /** Shorthand methods on ApiRequest that access a single param */
    private const SHORTHAND_METHODS = ['boolQueryParam'];

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

        $classReflection = $scope->getClassReflection();
        if (null === $classReflection || !$this->resolver->inGroup($classReflection->getName(), 'controller')) {
            return [];
        }

        if ([] === RouteAttributeHelper::getRouteAttributes($node)) {
            return [];
        }

        /** @var array<string, true> $params */
        $params = [];
        $this->findParamAccesses($node, $params);

        if (\count($params) <= 1) {
            return [];
        }

        return [
            RuleErrorBuilder::message(
                \sprintf(
                    'Controller method accesses %d request parameters directly (%s). Use a form type or DTO instead.',
                    \count($params),
                    implode(', ', array_keys($params)),
                ),
            )
                ->identifier('rentbetter.noMultipleRequestParamsInController')
                ->build(),
        ];
    }

    /** @param array<string, true> $params */
    private function findParamAccesses(Node $node, array &$params): void
    {
        if ($node instanceof MethodCall) {
            // $x->query->method('key') or $x->request->method('key')
            if (
                $node->var instanceof PropertyFetch
                && $node->var->name instanceof Identifier
                && \in_array($node->var->name->name, self::GETTER_PROPERTIES, true)
            ) {
                $args = $node->getArgs();
                if ([] !== $args && $args[0]->value instanceof String_) {
                    $params[$args[0]->value->value] = true;
                }
            }

            // $x->boolQueryParam('key')
            if (
                $node->name instanceof Identifier
                && \in_array($node->name->name, self::SHORTHAND_METHODS, true)
            ) {
                $args = $node->getArgs();
                if ([] !== $args && $args[0]->value instanceof String_) {
                    $params[$args[0]->value->value] = true;
                }
            }
        }

        foreach ($node->getSubNodeNames() as $subNodeName) {
            $subNode = $node->$subNodeName;
            if ($subNode instanceof Node) {
                $this->findParamAccesses($subNode, $params);
            } elseif (\is_array($subNode)) {
                foreach ($subNode as $child) {
                    if ($child instanceof Node) {
                        $this->findParamAccesses($child, $params);
                    }
                }
            }
        }
    }
}
