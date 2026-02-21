<?php

declare(strict_types=1);

namespace RentBetter\PHPStanRules\Rules\Serialization;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * jsonSerialize() methods should not return raw arrays.
 * Wrap the return array in array_filter_nulls() (or configured filter function)
 * to strip null values.
 *
 * @implements Rule<ClassMethod>
 */
final class NoNullInJsonSerializeRule implements Rule
{
    public function __construct(
        private readonly string $filterFunction = 'array_filter_nulls',
    ) {}

    public function getNodeType(): string
    {
        return ClassMethod::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if ('jsonSerialize' !== $node->name->name) {
            return [];
        }

        $errors = [];

        $returnStatements = $this->findReturnStatements($node);
        foreach ($returnStatements as $return) {
            if (null === $return->expr) {
                continue;
            }

            // Check if the return is a raw array literal
            if ($return->expr instanceof Node\Expr\Array_) {
                $errors[] = RuleErrorBuilder::message(\sprintf(
                    'jsonSerialize() returns a raw array. Wrap it in %s() to strip null values.',
                    $this->filterFunction,
                ))
                    ->identifier('rentbetter.noNullInJsonSerialize')
                    ->line($return->getStartLine())
                    ->build();
            }
        }

        return $errors;
    }

    /**
     * @return list<Return_>
     */
    private function findReturnStatements(Node $node): array
    {
        $returns = [];
        foreach ($node->getSubNodeNames() as $subNodeName) {
            $subNode = $node->$subNodeName;
            if ($subNode instanceof Return_) {
                $returns[] = $subNode;
            } elseif ($subNode instanceof Node) {
                // Don't descend into closures/anonymous classes
                if ($subNode instanceof Node\Expr\Closure || $subNode instanceof Node\Expr\ArrowFunction || $subNode instanceof Node\Stmt\Class_) {
                    continue;
                }
                $returns = [...$returns, ...$this->findReturnStatements($subNode)];
            } elseif (\is_array($subNode)) {
                foreach ($subNode as $child) {
                    if ($child instanceof Return_) {
                        $returns[] = $child;
                    } elseif ($child instanceof Node) {
                        if ($child instanceof Node\Expr\Closure || $child instanceof Node\Expr\ArrowFunction || $child instanceof Node\Stmt\Class_) {
                            continue;
                        }
                        $returns = [...$returns, ...$this->findReturnStatements($child)];
                    }
                }
            }
        }

        return $returns;
    }
}
