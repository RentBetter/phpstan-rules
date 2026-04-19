<?php

declare(strict_types=1);

namespace PTGS\PHPStanRules\Rules\Symfony;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PTGS\PHPStanRules\Rules\LevelAwareRule;

/**
 * Public methods with #[Route] attributes must end with "Action".
 *
 * @implements Rule<ClassMethod>
 */
final class ActionMethodNamingRule implements Rule
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

        if ([] === RouteAttributeHelper::getRouteAttributes($node)) {
            return [];
        }

        if (!$node->isPublic()) {
            return [];
        }

        $methodName = $node->name->name;

        if (!str_ends_with($methodName, 'Action')) {
            return [
                RuleErrorBuilder::message(\sprintf(
                    'Route method %s() should be named %sAction().',
                    $methodName,
                    $methodName,
                ))
                    ->identifier('ptgs.actionMethodNaming')
                    ->build(),
            ];
        }

        return [];
    }
}
