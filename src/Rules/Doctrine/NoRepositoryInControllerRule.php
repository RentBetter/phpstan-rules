<?php

declare(strict_types=1);

namespace RentBetter\PHPStanRules\Rules\Doctrine;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Controllers should not inject repository classes directly.
 * Use services instead to keep controllers thin.
 *
 * @implements Rule<Class_>
 */
final class NoRepositoryInControllerRule implements Rule
{
    public function getNodeType(): string
    {
        return Class_::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if (!$this->isController($node)) {
            return [];
        }

        $constructor = $node->getMethod('__construct');
        if (null === $constructor) {
            return [];
        }

        $errors = [];
        foreach ($constructor->params as $param) {
            $typeName = $this->getParamTypeName($param);
            if (null === $typeName) {
                continue;
            }

            if (str_ends_with($typeName, 'Repository')) {
                $className = $node->namespacedName?->toString() ?? (string) $node->name?->name;
                $errors[] = RuleErrorBuilder::message(\sprintf(
                    'Controller %s should not inject %s. Use a service instead.',
                    $className,
                    $typeName,
                ))
                    ->identifier('rentbetter.noRepositoryInController')
                    ->line($param->getStartLine())
                    ->build();
            }
        }

        return $errors;
    }

    private function isController(Class_ $node): bool
    {
        $name = $node->name?->name;
        if (null === $name) {
            return false;
        }

        return str_ends_with($name, 'Controller');
    }

    private function getParamTypeName(Node\Param $param): ?string
    {
        $type = $param->type;
        if ($type instanceof Node\Name) {
            return $type->toString();
        }
        if ($type instanceof Node\Identifier) {
            return $type->name;
        }

        return null;
    }
}
