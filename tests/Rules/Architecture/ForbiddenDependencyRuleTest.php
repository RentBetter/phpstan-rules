<?php

declare(strict_types=1);

namespace PTGS\PHPStanRules\Tests\Rules\Architecture;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PTGS\PHPStanRules\Rules\Architecture\ForbiddenDependencyRule;
use PTGS\PHPStanRules\Rules\NamespaceGroupResolver;

/**
 * @extends RuleTestCase<ForbiddenDependencyRule>
 */
final class ForbiddenDependencyRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        $resolver = new NamespaceGroupResolver([
            'controller' => ['~\\\\Controller\\\\~'],
            'service'    => ['~\\\\Services?\\\\~', '~\\\\Helpers?\\\\~'],
            'repository' => ['~\\\\Repository\\\\~'],
            'dbAccess'   => [
                'Doctrine\\ORM\\EntityManagerInterface',
                'Doctrine\\DBAL\\Connection',
            ],
        ]);

        return new ForbiddenDependencyRule(
            resolver: $resolver,
            forbiddenDependencies: [
                'controller' => [
                    ['group' => 'dbAccess', 'reason' => 'Controllers must remain thin — push DB access into a service.'],
                    ['group' => 'repository', 'reason' => 'Controllers should depend on services, not repositories directly.'],
                ],
                'service' => [
                    ['group' => 'dbAccess', 'reason' => 'Services must use repositories — repositories own all DB access.'],
                ],
            ],
        );
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/data/forbidden-dependency.php'], [
            [
                'Service App\Service\BadService may NOT depend on Doctrine\ORM\EntityManagerInterface (group: dbAccess). Services must use repositories — repositories own all DB access.',
                41,
            ],
            [
                'Service App\Service\BadService may NOT depend on Doctrine\DBAL\Connection (group: dbAccess). Services must use repositories — repositories own all DB access.',
                42,
            ],
            [
                'Controller App\Controller\BadController may NOT depend on Doctrine\ORM\EntityManagerInterface (group: dbAccess). Controllers must remain thin — push DB access into a service.',
                65,
            ],
            [
                'Controller App\Controller\BadController may NOT depend on Doctrine\DBAL\Connection (group: dbAccess). Controllers must remain thin — push DB access into a service.',
                66,
            ],
            [
                'Controller App\Controller\BadController may NOT depend on App\Repository\FooRepository (group: repository). Controllers should depend on services, not repositories directly.',
                67,
            ],
            // Helpers fall into the service group via the regex
            [
                'Service App\Helper\BadHelper may NOT depend on Doctrine\DBAL\Connection (group: dbAccess). Services must use repositories — repositories own all DB access.',
                87,
            ],
        ]);
    }
}
