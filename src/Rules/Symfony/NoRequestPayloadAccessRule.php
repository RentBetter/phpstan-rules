<?php

declare(strict_types=1);

namespace PTGS\PHPStanRules\Rules\Symfony;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\ObjectType;
use PTGS\PHPStanRules\Rules\LevelAwareRule;

/**
 * Forbid Request::toArray(), Request::getPayload(), and Request::getContent() anywhere.
 * Use the Form component to bind the request into a typed DTO instead.
 *
 * @implements Rule<MethodCall>
 */
final class NoRequestPayloadAccessRule implements Rule
{
    use LevelAwareRule;

    private const int MIN_LEVEL = 5;

    private const FLAGGED_METHODS = ['toArray', 'getPayload', 'getContent'];

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

        if (!$node->name instanceof Identifier) {
            return [];
        }

        $methodName = $node->name->name;
        if (!\in_array($methodName, self::FLAGGED_METHODS, true)) {
            return [];
        }

        $callerType = $scope->getType($node->var);
        $requestType = new ObjectType('Symfony\Component\HttpFoundation\Request');

        if (!$requestType->isSuperTypeOf($callerType)->yes()) {
            return [];
        }

        return [
            RuleErrorBuilder::message(
                \sprintf(
                    'Avoid Request::%s() — use the Form component to bind the request into a typed DTO instead.',
                    $methodName,
                ),
            )
                ->identifier('ptgs.noRequestPayloadAccess')
                ->build(),
        ];
    }
}
