<?php

declare(strict_types=1);

namespace PTGS\PHPStanRules\Rules\Doctrine;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\ObjectType;
use PTGS\PHPStanRules\Rules\LevelAwareRule;
use PTGS\PHPStanRules\Rules\NamespaceGroupResolver;

/**
 * Entities should not expose Doctrine Collections from public methods.
 * Return an array instead to avoid leaking persistence internals.
 *
 * @implements Rule<ClassMethod>
 */
final class NoPublicCollectionReturnRule implements Rule
{
    use LevelAwareRule;

    private const int MIN_LEVEL = 6;

    public function __construct(
        private readonly NamespaceGroupResolver $resolver,
        private readonly ?int $ruleLevel = null,
    ) {}

    public function getNodeType(): string
    {
        return ClassMethod::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if ($this->belowMinLevel()) {
            return [];
        }

        $classReflection = $scope->getClassReflection();
        if (!$classReflection instanceof ClassReflection) {
            return [];
        }

        if (!$this->isEntity($classReflection)) {
            return [];
        }

        if (!$node->isPublic()) {
            return [];
        }

        $methodName = $node->name->name;
        if (!$classReflection->hasMethod($methodName)) {
            return [];
        }

        $methodReflection = $classReflection->getMethod($methodName, $scope);
        $returnType = $methodReflection->getVariants()[0]->getReturnType();
        $collectionType = new ObjectType('Doctrine\Common\Collections\Collection');

        if ($collectionType->isSuperTypeOf($returnType)->yes()) {
            return [
                RuleErrorBuilder::message(\sprintf(
                    'Method %s::%s() should not return a Doctrine Collection. Return an array instead.',
                    $classReflection->getName(),
                    $methodName,
                ))
                    ->identifier('ptgs.noCollectionReturn')
                    ->build(),
            ];
        }

        return [];
    }

    private function isEntity(ClassReflection $classReflection): bool
    {
        return $this->resolver->inGroup($classReflection->getName(), 'entity');
    }
}
