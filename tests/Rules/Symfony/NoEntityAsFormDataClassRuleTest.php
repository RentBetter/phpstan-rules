<?php

declare(strict_types=1);

namespace RentBetter\PHPStanRules\Tests\Rules\Symfony;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use RentBetter\PHPStanRules\Rules\Symfony\NoEntityAsFormDataClassRule;
use RentBetter\PHPStanRules\Tests\Rules\TestGroups;

/** @extends RuleTestCase<NoEntityAsFormDataClassRule> */
final class NoEntityAsFormDataClassRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new NoEntityAsFormDataClassRule(TestGroups::defaultResolver());
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/data/entity-as-form-data-class.php'], [
            ['Form data_class must be a dedicated FormData DTO, not entity App\Entity\Tenancy.', 15],
            ['Form data_class must be a dedicated FormData DTO, not entity App\Tenancies\Tenancies\Entity\Tenancy.', 25],
        ]);
    }
}
