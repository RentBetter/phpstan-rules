<?php

declare(strict_types=1);

namespace RentBetter\PHPStanRules\Rules\Symfony;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use RentBetter\PHPStanRules\Rules\LevelAwareRule;

/**
 * Controllers must not inspect request payload directly.
 * Pass ApiRequest to a service instead.
 *
 * Bad:  $apiRequest->getJsonPayload()
 * Good: $this->myService->handle($apiRequest)
 *
 * @implements Rule<MethodCall>
 */
final class NoGetPayloadInControllerRule implements Rule
{
    use LevelAwareRule;

    private const int MIN_LEVEL = 5;

    public function __construct(
        private readonly ?int $ruleLevel = null,
    ) {}

    private const FLAGGED_METHODS = ['getJsonPayload', 'getPayload'];

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

        if (!\in_array($node->name->name, self::FLAGGED_METHODS, true)) {
            return [];
        }

        $classReflection = $scope->getClassReflection();
        if (null === $classReflection || !str_ends_with($classReflection->getName(), 'Controller')) {
            return [];
        }

        return [
            RuleErrorBuilder::message(
                'Avoid inspecting request payload in controllers. Pass ApiRequest to a service instead.',
            )
                ->identifier('rentbetter.noGetPayloadInController')
                ->build(),
        ];
    }
}
