<?php

declare(strict_types=1);

namespace PTGS\PHPStanRules\Tests\Rules\Doctrine;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PTGS\PHPStanRules\Rules\Doctrine\EntityTablePrefixRule;

/**
 * @extends RuleTestCase<EntityTablePrefixRule>
 */
final class EntityTablePrefixRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new EntityTablePrefixRule();
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/data/entity-table-prefix.php'], [
            [
                "Entity BadTableName table name 'things' must start with tbl_ — the prefix makes direct table usage searchable across the project.",
                7,
            ],
            [
                "Entity MissingTable is missing #[ORM\\Table(name: 'tbl_...')]. The tbl_ prefix makes direct table usage searchable across the project.",
                16,
            ],
        ]);
    }
}
