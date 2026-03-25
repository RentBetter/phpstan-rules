<?php

declare(strict_types=1);

namespace RentBetter\PHPStanRules\Rules;

/**
 * Trait for rules that should only fire at or above a certain PHPStan level.
 *
 * Usage:
 *   1. Add `use LevelAwareRule;` to the rule class
 *   2. Define `private const int MIN_LEVEL = N;`
 *   3. Add `private readonly ?int $ruleLevel = null` to the constructor
 *   4. Call `if ($this->belowMinLevel()) { return []; }` at the top of processNode()
 *
 * When ruleLevel is null (default), the rule always fires — backward compatible.
 */
trait LevelAwareRule
{
    private function belowMinLevel(): bool
    {
        return null !== $this->ruleLevel && $this->ruleLevel < self::MIN_LEVEL;
    }
}
