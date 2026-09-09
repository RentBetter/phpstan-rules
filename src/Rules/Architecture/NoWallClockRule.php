<?php

declare(strict_types=1);

namespace PTGS\PHPStanRules\Rules\Architecture;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PTGS\PHPStanRules\Rules\LevelAwareRule;

/**
 * The current time comes from the clock, never from the wall.
 *
 * `new DateTimeImmutable()`, `new DateTime()` and `new DatePoint()` with no argument, or with a
 * relative one ('now', 'today', '+1 day', 'next monday'), and `time()`, `microtime()`, `date()`
 * and friends read the system clock directly. Nothing can stand in for that in a test, and it
 * disagrees with what an injected ClockInterface reports under a MockClock — so a codebase
 * that does both has two notions of "now".
 *
 * Inject Symfony\Component\Clock\ClockInterface in a service; call the
 * Symfony\Component\Clock\now() function where there is no DI. Constructing an explicit value
 * — `new DateTimeImmutable('2025-01-15')`, `new DateTimeImmutable('@1700000000')` — reads
 * nothing and is not flagged; neither is a non-literal argument, which the rule cannot judge.
 *
 * @implements Rule<Expr>
 */
final class NoWallClockRule implements Rule
{
    use LevelAwareRule;

    private const int MIN_LEVEL = 8;

    private const array DATE_CLASSES = [
        'datetime',
        'datetimeimmutable',
        'symfony\component\clock\datepoint',
    ];

    private const array WALL_FUNCTIONS = [
        'time',
        'microtime',
        'date',
        'gmdate',
        'idate',
        'localtime',
        'getdate',
    ];

    public function __construct(
        private readonly ?int $ruleLevel = null,
    ) {}

    public function getNodeType(): string
    {
        return Expr::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if ($this->belowMinLevel()) {
            return [];
        }

        $offender = match (true) {
            $node instanceof New_ => $this->offendingConstruction($node, $scope),
            $node instanceof FuncCall => $this->offendingCall($node),
            default => null,
        };

        if (null === $offender) {
            return [];
        }

        return [
            RuleErrorBuilder::message(\sprintf(
                '%s reads the wall clock. Inject Symfony\Component\Clock\ClockInterface and use $this->clock->now(), '
                . 'or call Symfony\Component\Clock\now() where there is no DI, so the time is testable.',
                $offender,
            ))
                ->identifier('ptgs.noWallClock')
                ->build(),
        ];
    }

    private function offendingConstruction(New_ $node, Scope $scope): ?string
    {
        if (!$node->class instanceof Name) {
            return null;
        }

        $class = ltrim($scope->resolveName($node->class), '\\');
        if (!\in_array(strtolower($class), self::DATE_CLASSES, true)) {
            return null;
        }

        $parts = explode('\\', $class);
        $shortName = end($parts);

        if ([] === $node->args) {
            return \sprintf('new %s()', $shortName);
        }

        $first = $node->args[0];
        if (!$first instanceof Node\Arg || !$first->value instanceof String_) {
            return null;
        }

        // An absolute value starts with a digit ('2025-01-15') or '@' (an epoch). Anything else
        // — '', 'now', 'today', '+1 day', 'next monday' — is resolved against the moment of the
        // call, which is exactly the read this rule exists to stop.
        $value = $first->value->value;
        if ('' !== $value && (ctype_digit($value[0]) || '@' === $value[0])) {
            return null;
        }

        return \sprintf("new %s('%s')", $shortName, $value);
    }

    private function offendingCall(FuncCall $node): ?string
    {
        if (!$node->name instanceof Name) {
            return null;
        }

        $function = strtolower($node->name->getLast());
        if ($node->name->isQualified() && !$node->name->isFullyQualified()) {
            // A namespaced function of the same short name is somebody else's.
            return null;
        }

        if (!\in_array($function, self::WALL_FUNCTIONS, true)) {
            return null;
        }

        return \sprintf('%s()', $function);
    }
}
