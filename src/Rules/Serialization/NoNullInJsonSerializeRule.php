<?php

declare(strict_types=1);

namespace PTGS\PHPStanRules\Rules\Serialization;

use PhpParser\Node;
use PhpParser\Node\ArrayItem;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Stmt\Return_;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use PTGS\PHPStanRules\Rules\LevelAwareRule;

/**
 * jsonSerialize() must not return a raw array in which a top-level value may be null.
 * Wrap the return array in array_filter_nulls() (or the configured filter function)
 * so the key is dropped rather than serialised as null.
 *
 * Only values PHPStan can prove *may* be null count: a `?string` property, a call
 * returning `?T`, a spread array whose values include null. A value that can never be
 * null needs no filter, and one that is always null (a literal) is a deliberate choice.
 * `mixed` is not treated as nullable — being strict about mixed is PHPStan's own level 9
 * stance, and this rule fires from level 8.
 *
 * Judged at the return statement, so local variables and null-narrowing earlier in the
 * method are respected.
 *
 * @implements Rule<Return_>
 */
final class NoNullInJsonSerializeRule implements Rule
{
    use LevelAwareRule;

    private const int MIN_LEVEL = 8;

    public function __construct(
        private readonly string $filterFunction = 'array_filter_nulls',
        private readonly ?int $ruleLevel = null,
    ) {}

    public function getNodeType(): string
    {
        return Return_::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if ($this->belowMinLevel()) {
            return [];
        }

        if (!$node->expr instanceof Array_ || $scope->isInAnonymousFunction()) {
            return [];
        }

        $function = $scope->getFunction();
        if (!$function instanceof MethodReflection || 'jsonSerialize' !== $function->getName()) {
            return [];
        }

        $nullable = $this->nullableItems($node->expr, $scope);
        if ([] === $nullable) {
            return [];
        }

        return [
            RuleErrorBuilder::message(\sprintf(
                'jsonSerialize() returns a raw array where %s may be null. Wrap it in %s() to strip null values.',
                implode(', ', $nullable),
                $this->filterFunction,
            ))
                ->identifier('ptgs.noNullInJsonSerialize')
                ->line($node->getStartLine())
                ->build(),
        ];
    }

    /**
     * Describes each top-level item whose value may be null.
     *
     * @return list<string>
     */
    private function nullableItems(Array_ $array, Scope $scope): array
    {
        $nullable = [];
        foreach ($array->items as $index => $item) {
            $type = $scope->getType($item->value);
            if ($item->unpack) {
                $type = $type->getIterableValueType();
            }

            if ($this->mayBeNull($type)) {
                $nullable[] = $this->describe($item, $index);
            }
        }

        return $nullable;
    }

    private function mayBeNull(Type $type): bool
    {
        // MixedType also covers ErrorType (unresolvable) and template mixed
        if ($type instanceof MixedType) {
            return false;
        }

        return $type->isNull()->maybe();
    }

    private function describe(ArrayItem $item, int $index): string
    {
        if ($item->unpack) {
            return 'a spread value';
        }

        if ($item->key instanceof Node\Scalar\String_) {
            return \sprintf("'%s'", $item->key->value);
        }

        if ($item->key instanceof Node\Scalar\Int_) {
            return \sprintf('[%d]', $item->key->value);
        }

        return null === $item->key ? \sprintf('[%d]', $index) : 'a computed key';
    }
}
