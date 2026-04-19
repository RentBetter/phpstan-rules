<?php

declare(strict_types=1);

namespace PTGS\PHPStanRules\Rules\Doctrine;

use PhpParser\Node;
use PhpParser\Node\Stmt\Property;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PTGS\PHPStanRules\Rules\LevelAwareRule;

/**
 * Flags #[ORM\Column(type: ...)] where the type is already inferred from the PHP property type.
 *
 * Doctrine infers string→STRING, int→INTEGER, bool→BOOLEAN, float→FLOAT,
 * array→JSON, and DateTimeImmutable→DATETIME_IMMUTABLE automatically.
 * Specifying these explicitly is redundant noise.
 *
 * @implements Rule<Property>
 */
final class RedundantColumnTypeRule implements Rule
{
    use LevelAwareRule;

    private const int MIN_LEVEL = 6;

    public function __construct(
        private readonly ?int $ruleLevel = null,
    ) {}

    /**
     * Maps PHP type name → [redundant Doctrine constant name, redundant string literal].
     */
    private const REDUNDANT_MAP = [
        'string' => ['STRING', 'string'],
        'int' => ['INTEGER', 'integer'],
        'bool' => ['BOOLEAN', 'boolean'],
        'float' => ['FLOAT', 'float'],
        'array' => ['JSON', 'json'],
        'DateTimeImmutable' => ['DATETIME_IMMUTABLE', 'datetime_immutable'],
    ];

    public function getNodeType(): string
    {
        return Property::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if ($this->belowMinLevel()) {
            return [];
        }

        $columnAttr = $this->findColumnAttribute($node);
        if (null === $columnAttr) {
            return [];
        }

        $doctrineType = $this->getTypeArgument($columnAttr);
        if (null === $doctrineType) {
            return [];
        }

        $phpType = $this->getPhpTypeName($node);
        if (null === $phpType) {
            return [];
        }

        if (!isset(self::REDUNDANT_MAP[$phpType])) {
            return [];
        }

        [$redundantConstant, $redundantLiteral] = self::REDUNDANT_MAP[$phpType];

        if ($doctrineType['kind'] === 'constant' && $doctrineType['name'] === $redundantConstant) {
            return [
                RuleErrorBuilder::message(\sprintf(
                    'Redundant type: Types::%s on %s property $%s — Doctrine infers this from the PHP type.',
                    $redundantConstant,
                    $phpType,
                    $node->props[0]->name->name,
                ))
                    ->identifier('ptgs.redundantColumnType')
                    ->line($columnAttr->getStartLine())
                    ->build(),
            ];
        }

        if ($doctrineType['kind'] === 'string' && $doctrineType['value'] === $redundantLiteral) {
            return [
                RuleErrorBuilder::message(\sprintf(
                    "Redundant type: '%s' on %s property \$%s — Doctrine infers this from the PHP type.",
                    $redundantLiteral,
                    $phpType,
                    $node->props[0]->name->name,
                ))
                    ->identifier('ptgs.redundantColumnType')
                    ->line($columnAttr->getStartLine())
                    ->build(),
            ];
        }

        return [];
    }

    private function findColumnAttribute(Property $node): ?Node\Attribute
    {
        foreach ($node->attrGroups as $attrGroup) {
            foreach ($attrGroup->attrs as $attr) {
                $name = $attr->name->toString();
                if ('Column' === $name
                    || 'Doctrine\ORM\Mapping\Column' === $name
                    || 'ORM\Column' === $name
                ) {
                    return $attr;
                }
            }
        }

        return null;
    }

    /**
     * @return array{kind: 'constant', name: string}|array{kind: 'string', value: string}|null
     */
    private function getTypeArgument(Node\Attribute $attr): ?array
    {
        foreach ($attr->args as $arg) {
            if ($arg->name?->name !== 'type') {
                continue;
            }

            // Types::STRING style
            if ($arg->value instanceof Node\Expr\ClassConstFetch
                && $arg->value->name instanceof Node\Identifier
            ) {
                return ['kind' => 'constant', 'name' => $arg->value->name->name];
            }

            // 'string' style
            if ($arg->value instanceof Node\Scalar\String_) {
                return ['kind' => 'string', 'value' => $arg->value->value];
            }

            return null;
        }

        return null;
    }

    private function getPhpTypeName(Property $node): ?string
    {
        $type = $node->type;

        // ?string → string, ?DateTimeImmutable → DateTimeImmutable
        if ($type instanceof Node\NullableType) {
            $type = $type->type;
        }

        // string, int, bool, float, array
        if ($type instanceof Node\Identifier) {
            return $type->name;
        }

        // DateTimeImmutable, etc.
        if ($type instanceof Node\Name) {
            return $type->getLast();
        }

        return null;
    }
}
