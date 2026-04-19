<?php

declare(strict_types=1);

namespace PTGS\PHPStanRules\Rules\Doctrine;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PTGS\PHPStanRules\Rules\LevelAwareRule;

/**
 * Entities with #[ORM\Entity] must have #[ORM\Table(name: 'tbl_...')] with a tbl_ prefix.
 *
 * The tbl_ prefix makes it easy to search the project for direct table usage
 * (e.g. raw SQL, migrations) — grep for 'tbl_users' and you find every reference.
 *
 * @implements Rule<Class_>
 */
final class EntityTablePrefixRule implements Rule
{
    use LevelAwareRule;

    private const int MIN_LEVEL = 6;

    public function __construct(
        private readonly ?int $ruleLevel = null,
    ) {}

    public function getNodeType(): string
    {
        return Class_::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if ($this->belowMinLevel()) {
            return [];
        }

        if (!$this->hasAttribute($node, 'Entity')) {
            return [];
        }

        $tableAttr = $this->findAttribute($node, 'Table');

        if (null === $tableAttr) {
            return [
                RuleErrorBuilder::message(\sprintf(
                    'Entity %s is missing #[ORM\\Table(name: \'tbl_...\')]. The tbl_ prefix makes direct table usage searchable across the project.',
                    $node->name->name ?? '(anonymous)',
                ))
                    ->identifier('ptgs.entityTablePrefix')
                    ->build(),
            ];
        }

        $name = $this->getNamedArgStringValue($tableAttr, 'name');
        if (null === $name) {
            // name: given as first positional arg
            if (isset($tableAttr->args[0]) && null === $tableAttr->args[0]->name) {
                $value = $tableAttr->args[0]->value;
                if ($value instanceof Node\Scalar\String_) {
                    $name = $value->value;
                }
            }
        }

        if (null === $name) {
            return [
                RuleErrorBuilder::message(\sprintf(
                    'Entity %s has #[ORM\\Table] without a name argument. Specify name: \'tbl_...\'.',
                    $node->name->name ?? '(anonymous)',
                ))
                    ->identifier('ptgs.entityTablePrefix')
                    ->line($tableAttr->getStartLine())
                    ->build(),
            ];
        }

        if (!str_starts_with($name, 'tbl_')) {
            return [
                RuleErrorBuilder::message(\sprintf(
                    'Entity %s table name \'%s\' must start with tbl_ — the prefix makes direct table usage searchable across the project.',
                    $node->name->name ?? '(anonymous)',
                    $name,
                ))
                    ->identifier('ptgs.entityTablePrefix')
                    ->line($tableAttr->getStartLine())
                    ->build(),
            ];
        }

        return [];
    }

    private function hasAttribute(Class_ $node, string $shortName): bool
    {
        return null !== $this->findAttribute($node, $shortName);
    }

    private function findAttribute(Class_ $node, string $shortName): ?Node\Attribute
    {
        foreach ($node->attrGroups as $attrGroup) {
            foreach ($attrGroup->attrs as $attr) {
                $name = $attr->name->toString();
                if ($shortName === $name
                    || "Doctrine\\ORM\\Mapping\\{$shortName}" === $name
                    || "ORM\\{$shortName}" === $name
                ) {
                    return $attr;
                }
            }
        }

        return null;
    }

    private function getNamedArgStringValue(Node\Attribute $attr, string $name): ?string
    {
        foreach ($attr->args as $arg) {
            if ($arg->name?->name === $name) {
                $value = $arg->value;
                if ($value instanceof Node\Scalar\String_) {
                    return $value->value;
                }
            }
        }

        return null;
    }
}
