<?php

declare(strict_types=1);

namespace RentBetter\PHPStanRules\Rules\Architecture;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\ObjectType;
use RentBetter\PHPStanRules\Rules\LevelAwareRule;

/**
 * Use DateFormatter::formatDatetime() or DateFormatter::formatDate()
 * instead of calling ->format() on DateTime objects directly.
 *
 * This ensures consistent RFC3339 formatting and respects user timezone
 * from request headers.
 *
 * @implements Rule<MethodCall>
 */
final class UseDateFormatterRule implements Rule
{
    use LevelAwareRule;

    private const int MIN_LEVEL = 6;

    public function __construct(
        private readonly ?int $ruleLevel = null,
    ) {}

    public function getNodeType(): string
    {
        return MethodCall::class;
    }

    /** @return list<IdentifierRuleError> */
    public function processNode(Node $node, Scope $scope): array
    {
        if ($this->belowMinLevel()) {
            return [];
        }

        if (!$node->name instanceof Identifier || 'format' !== $node->name->name) {
            return [];
        }

        $callerType = $scope->getType($node->var);
        $dateTimeType = new ObjectType(\DateTimeInterface::class);

        if (!$dateTimeType->isSuperTypeOf($callerType)->yes()) {
            return [];
        }

        // Allow inside DateFormatter itself
        $classReflection = $scope->getClassReflection();
        if (null !== $classReflection && str_contains($classReflection->getName(), 'DateFormatter')) {
            return [];
        }

        return [
            RuleErrorBuilder::message(
                'Use DateFormatter::formatDatetime() or DateFormatter::formatDate() instead of ->format().',
            )
                ->identifier('rentbetter.useDateFormatter')
                ->build(),
        ];
    }
}
