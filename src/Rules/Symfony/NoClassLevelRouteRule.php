<?php

declare(strict_types=1);

namespace RentBetter\PHPStanRules\Rules\Symfony;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use RentBetter\PHPStanRules\Rules\LevelAwareRule;

/**
 * Forbids #[Route] on the controller class itself.
 *
 * Class-level route prefixes make the full path invisible at the method level
 * and impossible to search for in the codebase. Every route path should be
 * defined in full on the action method.
 *
 * @implements Rule<Class_>
 */
final class NoClassLevelRouteRule implements Rule
{
    use LevelAwareRule;

    private const int MIN_LEVEL = 5;

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

        foreach ($node->attrGroups as $attrGroup) {
            foreach ($attrGroup->attrs as $attr) {
                $name = $attr->name->toString();
                if ('Route' === $name
                    || 'Symfony\Component\Routing\Annotation\Route' === $name
                    || 'Symfony\Component\Routing\Attribute\Route' === $name
                ) {
                    return [
                        RuleErrorBuilder::message(\sprintf(
                            'Class %s has a class-level #[Route] attribute. Define the full route path on each action method instead — class-level prefixes hide the real path and make routes unsearchable.',
                            $node->name->name ?? '(anonymous)',
                        ))
                            ->identifier('rentbetter.noClassLevelRoute')
                            ->line($attr->getStartLine())
                            ->build(),
                    ];
                }
            }
        }

        return [];
    }
}
