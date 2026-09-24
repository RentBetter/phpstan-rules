<?php

declare(strict_types=1);

namespace PTGS\PHPStanRules\Rules\Architecture;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\InterpolatedStringPart;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\InterpolatedString;
use PhpParser\PrettyPrinter\Standard;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\Type;
use PTGS\PHPStanRules\Rules\LevelAwareRule;

/**
 * The current time comes from the clock, never from the wall.
 *
 * `new DateTimeImmutable()`, `new DateTime()` and `new DatePoint()` with no argument, or with one
 * PHP resolves against the moment of the call ('now', 'today', '+1 day', '10:00'), and `time()`,
 * `date()` and friends called without a timestamp, read the system clock directly. Nothing can
 * stand in for that in a test, and it disagrees with what an injected ClockInterface reports under
 * a MockClock — so a codebase that does both has two notions of "now".
 *
 * Inject Psr\Clock\ClockInterface in a service; call Symfony\Component\Clock\Clock::get()->now()
 * where there is no DI.
 *
 * The argument need not be a literal. A constant, a concatenation, an interpolation or a
 * `sprintf()` — `'-' . self::TTL . ' seconds'`, `"-{$days} days"`, `sprintf('-%d days', $days)` —
 * is judged on the string it builds, with any number the analyser cannot pin down stood in by a
 * sample. A part that could be any string (`$row['created_at']`, `$date . ' 10:00'`) cannot be
 * judged, so that construction is left alone; so is an explicit value — `'2025-01-15'`,
 * `'@1700000000'`.
 *
 * Deliberately not flagged: `microtime()`, which in practice is a stopwatch — on a frozen clock
 * every duration would be zero — and `date()` and friends given a timestamp, which format a value
 * rather than read one.
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

    /**
     * Each function that reads the wall, mapped to the position of the timestamp argument that
     * makes it format that value instead (null: it reads the wall whatever it is given).
     */
    private const array WALL_FUNCTIONS = [
        'time' => null,
        'date' => 1,
        'gmdate' => 1,
        'idate' => 1,
        'localtime' => 0,
        'getdate' => 0,
    ];

    /**
     * Stands in for a number the analyser cannot pin down. Two digits read as a year, a month, a
     * day, an hour or a count alike, so '{$year}-07-01' stays a date and '-{$days} days' an offset.
     */
    private const string SAMPLE_NUMBER = '12';

    private readonly Standard $printer;

    public function __construct(
        private readonly ?int $ruleLevel = null,
    ) {
        $this->printer = new Standard();
    }

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
            $node instanceof FuncCall => $this->offendingCall($node, $scope),
            default => null,
        };

        if (null === $offender) {
            return [];
        }

        return [
            RuleErrorBuilder::message(\sprintf(
                '%s reads the wall clock. Inject Psr\Clock\ClockInterface and use $this->clock->now(), '
                . 'or call Symfony\Component\Clock\Clock::get()->now() where there is no DI, so the time is testable.',
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
        if (!$first instanceof Arg || $first->unpack) {
            return null;
        }

        foreach ($this->candidateValues($first->value, $scope) as $value) {
            if (self::isRelative($value)) {
                return \sprintf('new %s(%s)', $shortName, $this->printer->prettyPrintExpr($first->value));
            }
        }

        return null;
    }

    private function offendingCall(FuncCall $node, Scope $scope): ?string
    {
        if (!$node->name instanceof Name) {
            return null;
        }

        $function = strtolower($node->name->getLast());
        if ($node->name->isQualified() && !$node->name->isFullyQualified()) {
            // A namespaced function of the same short name is somebody else's.
            return null;
        }

        if (!\array_key_exists($function, self::WALL_FUNCTIONS)) {
            return null;
        }

        $timestampPosition = self::WALL_FUNCTIONS[$function];
        if (null !== $timestampPosition && $this->passesTimestamp($node, $timestampPosition, $scope)) {
            return null;
        }

        return \sprintf('%s()', $function);
    }

    /**
     * Whether the call hands the function a timestamp to format. An explicit null does not count:
     * PHP reads the clock for it, exactly as for an omitted argument.
     */
    private function passesTimestamp(FuncCall $call, int $position, Scope $scope): bool
    {
        foreach ($call->args as $index => $arg) {
            if (!$arg instanceof Arg) {
                continue;
            }

            if ($arg->unpack) {
                // The spread may well carry the timestamp; there is no telling.
                return true;
            }

            $isTimestamp = null !== $arg->name
                ? 'timestamp' === $arg->name->toLowerString()
                : $index === $position;

            if ($isTimestamp) {
                return !$scope->getType($arg->value)->isNull()->yes();
            }
        }

        return false;
    }

    /**
     * The strings an argument can hold, as far as the analyser can tell: every value of a constant
     * type, or the one string a concatenation, interpolation or `sprintf()` builds.
     *
     * @return list<string>
     */
    private function candidateValues(Expr $expr, Scope $scope): array
    {
        $constants = $scope->getType($expr)->getConstantStrings();
        if ([] !== $constants) {
            return array_map(static fn ($constant): string => $constant->getValue(), $constants);
        }

        $built = $this->builtString($expr, $scope);

        return null === $built ? [] : [$built];
    }

    /**
     * The string a concatenation, an interpolation or a `sprintf()` builds, each number the
     * analyser cannot pin down replaced by a sample. Null for any other expression, and when a part
     * could be any string at all — there is nothing to judge then.
     */
    private function builtString(Expr $expr, Scope $scope): ?string
    {
        return match (true) {
            $expr instanceof InterpolatedString => $this->joinParts($expr->parts, $scope),
            $expr instanceof Concat => $this->joinParts([$expr->left, $expr->right], $scope),
            $expr instanceof FuncCall => $this->sprintfString($expr, $scope),
            default => null,
        };
    }

    /**
     * @param array<Expr|InterpolatedStringPart> $parts
     */
    private function joinParts(array $parts, Scope $scope): ?string
    {
        $joined = '';
        foreach ($parts as $part) {
            $value = $part instanceof InterpolatedStringPart ? $part->value : $this->partValue($part, $scope);
            if (null === $value) {
                return null;
            }

            $joined .= $value;
        }

        return $joined;
    }

    /**
     * What one part contributes to the string: its value when the analyser knows it (the first, if
     * it could be one of several), a sample when it is a number, or null when it could be anything.
     */
    private function partValue(Expr $expr, Scope $scope): ?string
    {
        $type = $scope->getType($expr);

        $known = $type->getConstantScalarValues();
        if ([] !== $known) {
            return (string) $known[0];
        }

        if (self::isNumber($type)) {
            return self::SAMPLE_NUMBER;
        }

        return $this->builtString($expr, $scope);
    }

    /**
     * The string `sprintf()` builds from a format the analyser knows, each conversion filled from
     * its argument: a sample for a numeric conversion, the argument's own part value for `%s`.
     */
    private function sprintfString(FuncCall $call, Scope $scope): ?string
    {
        if (!$call->name instanceof Name || 'sprintf' !== strtolower($call->name->getLast()) || $call->isFirstClassCallable()) {
            return null;
        }

        if ($call->name->isQualified() && !$call->name->isFullyQualified()) {
            return null;
        }

        $args = $call->getArgs();
        foreach ($args as $arg) {
            if ($arg->unpack || null !== $arg->name) {
                return null;
            }
        }

        $formats = [] === $args ? [] : $scope->getType($args[0]->value)->getConstantStrings();
        if (1 !== \count($formats)) {
            return null;
        }

        $next = 1;
        $unknown = false;
        $built = preg_replace_callback(
            '/%(?:(\d+)\$)?(?:[-+ 0]|\'.)*\d*(?:\.\d+)?([%bcdeEfFgGhHosuxX])/',
            function (array $match) use ($args, $scope, &$next, &$unknown): string {
                $conversion = $match[2];
                if ('%' === $conversion) {
                    return '%';
                }

                $position = '' !== $match[1] ? (int) $match[1] : $next++;
                if (!isset($args[$position])) {
                    $unknown = true;

                    return '';
                }

                if ('s' !== $conversion) {
                    return self::SAMPLE_NUMBER;
                }

                $value = $this->partValue($args[$position]->value, $scope);
                if (null === $value) {
                    $unknown = true;

                    return '';
                }

                return $value;
            },
            $formats[0]->getValue(),
        );

        return $unknown || null === $built ? null : $built;
    }

    private static function isNumber(Type $type): bool
    {
        return $type->isInteger()->yes() || $type->isFloat()->yes() || $type->isNumericString()->yes();
    }

    /**
     * Whether PHP resolves this date string against the moment it is parsed. It does exactly when
     * the string leaves the year, the month or the day to be filled in from now: '10:00' is today
     * at ten, '15 January' is this year's, '2024' is today at 20:24. A string that is not a date
     * throws rather than reading anything — except the empty string, which means now.
     */
    private static function isRelative(string $value): bool
    {
        if ('' === trim($value)) {
            return true;
        }

        $parsed = date_parse($value);
        if ($parsed['error_count'] > 0) {
            return false;
        }

        return false === $parsed['year'] || false === $parsed['month'] || false === $parsed['day'];
    }
}
