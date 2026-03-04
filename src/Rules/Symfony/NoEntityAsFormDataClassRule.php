<?php

declare(strict_types=1);

namespace RentBetter\PHPStanRules\Rules\Symfony;

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
    public function __construct(
        private readonly string $entityNamespaceSegment = 'Entity',
    ) {}

    public function getNodeType(): string
    {
        return MethodCall::class;
    }

    /** @return list<IdentifierRuleError> */
    public function processNode(Node $node, Scope $scope): array
    {
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

            if ($this->isEntityClass($className)) {
                return [
                    RuleErrorBuilder::message(
                        \sprintf(
                            'Form data_class must be a dedicated FormData DTO, not entity %s.',
                            $className,
                        ),
                    )
                        ->identifier('rentbetter.noEntityAsFormDataClass')
                        ->build(),
                ];
            }
        }

        return [];
    }

    private function isEntityClass(string $className): bool
    {
        // Check if "Entity" appears as a namespace segment
        $segments = explode('\\', $className);
        array_pop($segments); // Remove the class name itself

        return \in_array($this->entityNamespaceSegment, $segments, true);
    }
}
