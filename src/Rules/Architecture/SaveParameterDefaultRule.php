<?php

declare(strict_types=1);

namespace RentBetter\PHPStanRules\Rules\Architecture;

use PhpParser\Node;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Public methods with a $save or $flush bool parameter should default to true.
 *
 * Good: public function create(Entity $e, bool $save = true): void
 * Bad:  public function create(Entity $e, bool $save = false): void
 *
 * @implements Rule<ClassMethod>
 */
final class SaveParameterDefaultRule implements Rule
{
    private const PARAM_NAMES = ['save', 'flush'];

    public function getNodeType(): string
    {
        return ClassMethod::class;
    }

    /** @return list<IdentifierRuleError> */
    public function processNode(Node $node, Scope $scope): array
    {
        if (!$node->isPublic()) {
            return [];
        }

        $errors = [];

        foreach ($node->params as $param) {
            if (!$param->var instanceof Node\Expr\Variable || !\is_string($param->var->name)) {
                continue;
            }

            $name = $param->var->name;
            if (!\in_array($name, self::PARAM_NAMES, true)) {
                continue;
            }

            // Must be bool typed
            if (!$param->type instanceof Node\Identifier || 'bool' !== $param->type->name) {
                continue;
            }

            // No default = required param, that's fine
            if (null === $param->default) {
                continue;
            }

            if (
                $param->default instanceof ConstFetch
                && 'false' === $param->default->name->toLowerString()
            ) {
                $errors[] = RuleErrorBuilder::message(
                    \sprintf('Public method $%s parameter should default to true, not false.', $name),
                )
                    ->identifier('rentbetter.saveParameterDefault')
                    ->build();
            }
        }

        return $errors;
    }
}
