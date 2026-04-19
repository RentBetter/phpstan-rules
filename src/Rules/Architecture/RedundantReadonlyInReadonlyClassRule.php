<?php

declare(strict_types=1);

namespace PTGS\PHPStanRules\Rules\Architecture;

use PhpParser\Node;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleError;
use PHPStan\Rules\RuleErrorBuilder;
use PTGS\PHPStanRules\Rules\LevelAwareRule;

/**
 * In a readonly class, per-property readonly modifiers are redundant noise.
 *
 * @implements Rule<Class_>
 */
final class RedundantReadonlyInReadonlyClassRule implements Rule
{
    use LevelAwareRule;

    private const int MIN_LEVEL = 8;

    public function __construct(
        private readonly ?int $ruleLevel = null,
    ) {}

    public function getNodeType(): string
    {
        return Class_::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if ($this->belowMinLevel() || !$node->isReadonly()) {
            return [];
        }

        $errors = [];

        foreach ($node->stmts as $stmt) {
            if ($stmt instanceof Property && $stmt->isReadonly()) {
                foreach ($stmt->props as $prop) {
                    $errors[] = $this->buildError(
                        \sprintf(
                            'Property $%s must not be declared readonly inside readonly class %s.',
                            $prop->name->toString(),
                            $this->className($node),
                        ),
                        $stmt->getStartLine(),
                    );
                }
            }
        }

        $constructor = $node->getMethod('__construct');
        if (null === $constructor) {
            return $errors;
        }

        foreach ($constructor->params as $param) {
            if ($this->isPromotedReadonlyParam($param)) {
                $errors[] = $this->buildError(
                    \sprintf(
                        'Promoted property $%s must not be declared readonly inside readonly class %s.',
                        $param->var->name,
                        $this->className($node),
                    ),
                    $param->getStartLine(),
                );
            }
        }

        return $errors;
    }

    private function isPromotedReadonlyParam(Param $param): bool
    {
        $isPromoted = 0 !== ($param->flags & Class_::VISIBILITY_MODIFIER_MASK);

        return $isPromoted && 0 !== ($param->flags & Class_::MODIFIER_READONLY);
    }

    private function className(Class_ $node): string
    {
        return $node->namespacedName?->toString() ?? $node->name?->toString() ?? 'anonymous class';
    }

    private function buildError(string $message, int $line): RuleError
    {
        return RuleErrorBuilder::message($message)
            ->identifier('ptgs.redundantReadonlyInReadonlyClass')
            ->line($line)
            ->build();
    }
}
