<?php

declare(strict_types=1);

namespace PTGS\PHPStanRules\Rules\Doctrine;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\Int_;
use PhpParser\Node\Scalar\String_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PTGS\PHPStanRules\Rules\LevelAwareRule;

/**
 * Detects hardcoded numeric/string values in DQL queries that should use
 * bound parameters (:param) or enum backing values ($enum->value).
 *
 * Covers two patterns:
 *
 * 1. DQL string literals:
 *    Bad:  ->andWhere('t.status IN (0, 2, 3)')
 *    Good: ->andWhere('t.status IN (:statuses)')
 *
 * 2. Expression builder calls:
 *    Bad:  ->expr()->in('t.status', [0, 2, 3])
 *    Bad:  ->expr()->eq('t.status', 0)
 *    Good: ->expr()->in('t.status', ':statuses')
 *    Good: ->expr()->eq('t.status', ':status')
 *
 * @implements Rule<MethodCall>
 */
final class NoHardcodedValueInQueryRule implements Rule
{
    use LevelAwareRule;

    private const int MIN_LEVEL = 6;

    public function __construct(
        private readonly ?int $ruleLevel = null,
    ) {}

    private const DQL_METHODS = [
        'createQuery',
        'setDQL',
        'where',
        'andWhere',
        'orWhere',
        'having',
        'andHaving',
        'orHaving',
    ];

    /** expr() comparison methods where 2nd arg is the value */
    private const EXPR_COMPARISON_METHODS = ['eq', 'neq'];

    /** expr() collection methods where 2nd arg is an array of values */
    private const EXPR_COLLECTION_METHODS = ['in', 'notIn'];

    /** Matches: IN (0, 2, 3), NOT IN (1, 2) — but not IN (:param) or IN (?1) */
    private const IN_CLAUSE_PATTERN = '/\bIN\s*\(\s*-?\d+/i';

    /** Matches: t.status = 0, t.type != 2 — dotted identifier compared to bare number */
    private const COMPARISON_PATTERN = '/\b\w+\.\w+\s*(?:!=|<>|=)\s*-?\d+\b/';

    public function getNodeType(): string
    {
        return MethodCall::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if ($this->belowMinLevel()) {
            return [];
        }

        if (!$node->name instanceof Identifier) {
            return [];
        }

        $methodName = $node->name->name;

        if (\in_array($methodName, self::DQL_METHODS, true)) {
            return $this->checkDqlStringArgs($node);
        }

        if (\in_array($methodName, self::EXPR_COMPARISON_METHODS, true)) {
            return $this->checkExprComparison($node);
        }

        if (\in_array($methodName, self::EXPR_COLLECTION_METHODS, true)) {
            return $this->checkExprCollection($node);
        }

        return [];
    }

    /** @return list<IdentifierRuleError> */
    private function checkDqlStringArgs(MethodCall $node): array
    {
        $errors = [];

        foreach ($node->getArgs() as $arg) {
            if (!$arg->value instanceof String_) {
                continue;
            }

            $dql = $arg->value->value;

            if (preg_match(self::IN_CLAUSE_PATTERN, $dql)) {
                $errors[] = RuleErrorBuilder::message(
                    'Hardcoded numeric value in DQL IN() clause. Use a bound parameter (:param) or $enum->value instead.',
                )
                    ->identifier('ptgs.noHardcodedValueInQuery')
                    ->build();
            } elseif (preg_match(self::COMPARISON_PATTERN, $dql)) {
                $errors[] = RuleErrorBuilder::message(
                    'Hardcoded numeric value in DQL comparison. Use a bound parameter (:param) or $enum->value instead.',
                )
                    ->identifier('ptgs.noHardcodedValueInQuery')
                    ->build();
            }
        }

        return $errors;
    }

    /** @return list<IdentifierRuleError> */
    private function checkExprComparison(MethodCall $node): array
    {
        $args = $node->getArgs();
        if (\count($args) < 2) {
            return [];
        }

        $value = $args[1]->value;

        if ($value instanceof Int_) {
            return [$this->buildExprError()];
        }

        if ($value instanceof String_ && !$this->isParameterReference($value->value)) {
            return [$this->buildExprError()];
        }

        return [];
    }

    /** @return list<IdentifierRuleError> */
    private function checkExprCollection(MethodCall $node): array
    {
        $args = $node->getArgs();
        if (\count($args) < 2) {
            return [];
        }

        $value = $args[1]->value;

        if (!$value instanceof Array_) {
            return [];
        }

        foreach ($value->items as $item) {
            if ($item->value instanceof Int_) {
                return [$this->buildExprError()];
            }

            if ($item->value instanceof String_ && !$this->isParameterReference($item->value->value)) {
                return [$this->buildExprError()];
            }
        }

        return [];
    }

    /** Strings starting with : or ? are parameter references, not magic values. */
    private function isParameterReference(string $value): bool
    {
        return str_starts_with($value, ':') || str_starts_with($value, '?');
    }

    private function buildExprError(): IdentifierRuleError
    {
        return RuleErrorBuilder::message(
            'Hardcoded value in query expression. Use a bound parameter (:param) or $enum->value instead.',
        )
            ->identifier('ptgs.noHardcodedValueInQuery')
            ->build();
    }
}
