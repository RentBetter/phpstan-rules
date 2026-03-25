<?php

declare(strict_types=1);

namespace RentBetter\PHPStanRules\Rules\Serialization;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use RentBetter\PHPStanRules\Rules\LevelAwareRule;

/**
 * JSON keys in jsonSerialize() methods must use camelCase, not snake_case.
 *
 * @implements Rule<ClassMethod>
 */
final class NoSnakeCaseJsonKeyRule implements Rule
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

    public function processNode(Node $node, Scope $scope): array
    {
        if ($this->belowMinLevel()) {
            return [];
        }

        if ('jsonSerialize' !== $node->name->name) {
            return [];
        }

        $errors = [];
        $this->findSnakeCaseKeys($node, $errors);

        return $errors;
    }

    /**
     * @param list<\PHPStan\Rules\IdentifierRuleError> $errors
     */
    private function findSnakeCaseKeys(Node $node, array &$errors): void
    {
        if ($node instanceof Node\Expr\Array_) {
            foreach ($node->items as $item) {
                if ($item->key instanceof Node\Scalar\String_ && $this->isSnakeCase($item->key->value)) {
                    $errors[] = RuleErrorBuilder::message(\sprintf(
                        'JSON key \'%s\' in jsonSerialize() uses snake_case. Use camelCase instead.',
                        $item->key->value,
                    ))
                        ->identifier('rentbetter.noSnakeCaseJsonKey')
                        ->line($item->key->getStartLine())
                        ->build();
                }
            }
        }

        foreach ($node->getSubNodeNames() as $subNodeName) {
            $subNode = $node->$subNodeName;
            if ($subNode instanceof Node) {
                $this->findSnakeCaseKeys($subNode, $errors);
            } elseif (\is_array($subNode)) {
                foreach ($subNode as $child) {
                    if ($child instanceof Node) {
                        $this->findSnakeCaseKeys($child, $errors);
                    }
                }
            }
        }
    }

    private function isSnakeCase(string $key): bool
    {
        return str_contains($key, '_') && strtolower($key) === $key;
    }
}
