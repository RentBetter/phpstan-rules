<?php

declare(strict_types=1);

namespace PTGS\PHPStanRules\Rules\Symfony;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PTGS\PHPStanRules\Rules\LevelAwareRule;
use PTGS\PHPStanRules\Rules\NamespaceGroupResolver;

/**
 * Form types must use a dedicated FormData DTO as data_class, not an entity.
 *
 * Bad:  $resolver->setDefaults(['data_class' => Tenancy::class])
 * Good: $resolver->setDefaults(['data_class' => TenancyFormData::class])
 *
 * @implements Rule<MethodCall>
 */
final class NoEntityAsFormDataClassRule implements Rule
{
    use LevelAwareRule;

    private const int MIN_LEVEL = 5;

    public function __construct(
        private readonly NamespaceGroupResolver $resolver,
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

        if (!$node->name instanceof Identifier || 'setDefaults' !== $node->name->name) {
            return [];
        }

        $args = $node->getArgs();
        if ([] === $args) {
            return [];
        }

        $defaults = $args[0]->value;
        if (!$defaults instanceof Array_) {
            return [];
        }

        foreach ($defaults->items as $item) {
            if (null === $item->key || !$item->key instanceof String_ || 'data_class' !== $item->key->value) {
                continue;
            }

            // data_class => SomeClass::class
            if (!$item->value instanceof ClassConstFetch || !$item->value->class instanceof Name) {
                continue;
            }

            $className = $item->value->class->toString();

            if ($this->resolver->inGroup($className, 'entity')) {
                return [
                    RuleErrorBuilder::message(
                        \sprintf(
                            'Form data_class must be a dedicated FormData DTO, not entity %s.',
                            $className,
                        ),
                    )
                        ->identifier('ptgs.noEntityAsFormDataClass')
                        ->build(),
                ];
            }
        }

        return [];
    }
}
