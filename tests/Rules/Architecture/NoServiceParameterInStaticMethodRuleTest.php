<?php

declare(strict_types=1);

namespace PTGS\PHPStanRules\Tests\Rules\Architecture;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PTGS\PHPStanRules\Rules\Architecture\NoServiceParameterInStaticMethodRule;
use PTGS\PHPStanRules\Tests\Rules\TestGroups;

/**
 * @extends RuleTestCase<NoServiceParameterInStaticMethodRule>
 */
final class NoServiceParameterInStaticMethodRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new NoServiceParameterInStaticMethodRule(TestGroups::defaultResolver());
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/data/no-service-parameter-in-static-method.php'], [
            [
                'Static method fromRequest() takes App\Service\LeadService (group: service) as a parameter. Dependencies enter a class through its constructor — make this an instance method, or let the caller that already has LeadService injected do the work.',
                32,
            ],
            [
                'Static method build() takes App\Service\LeadService (group: service) as a parameter. Dependencies enter a class through its constructor — make this an instance method, or let the caller that already has LeadService injected do the work.',
                42,
            ],
            [
                'Static method build() takes App\Repository\LeadRepository (group: repository) as a parameter. Dependencies enter a class through its constructor — make this an instance method, or let the caller that already has LeadRepository injected do the work.',
                43,
            ],
            [
                'Static method build() takes Doctrine\ORM\EntityManagerInterface (group: dbAccess) as a parameter. Dependencies enter a class through its constructor — make this an instance method, or let the caller that already has EntityManagerInterface injected do the work.',
                44,
            ],
            [
                'Static method maybe() takes App\Service\ThingHelper (group: service) as a parameter. Dependencies enter a class through its constructor — make this an instance method, or let the caller that already has ThingHelper injected do the work.',
                52,
            ],
        ]);
    }
}
