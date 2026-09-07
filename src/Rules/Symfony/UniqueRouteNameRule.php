<?php

declare(strict_types=1);

namespace PTGS\PHPStanRules\Rules\Symfony;

use PhpParser\Node;
use PHPStan\Node\CollectedDataNode;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Errors when two routes declare the same explicit name. Only authoritative on a
 * full analyse — a path-scoped run can only see collisions among the analysed files.
 *
 * @implements Rule<CollectedDataNode>
 */
final class UniqueRouteNameRule implements Rule
{
    public function getNodeType(): string
    {
        return CollectedDataNode::class;
    }

    public function processNode(Node $node, $scope): array
    {
        $declarations = [];
        foreach ($node->get(RouteNameCollector::class) as $file => $collected) {
            foreach ($collected as $names) {
                foreach ($names as [$name, $line]) {
                    $declarations[$name][] = [$file, $line];
                }
            }
        }

        $errors = [];
        foreach ($declarations as $name => $sites) {
            if (count($sites) < 2) {
                continue;
            }

            foreach ($sites as [$file, $line]) {
                $others = array_values(array_filter($sites, fn(array $site) => $site !== [$file, $line]));
                $errors[] = RuleErrorBuilder::message(sprintf(
                    "Route name '%s' is also declared in %s — Symfony keeps only the last-registered route and silently 404s the rest. Route names are global: disambiguate with a domain prefix.",
                    $name,
                    implode(', ', array_map(fn(array $site) => $site[0] . ':' . $site[1], $others)),
                ))
                    ->identifier('ptgs.uniqueRouteName')
                    ->file($file)
                    ->line($line)
                    ->build()
                ;
            }
        }

        return $errors;
    }
}
