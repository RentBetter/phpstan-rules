<?php

declare(strict_types=1);

namespace RentBetter\PHPStanRules\Rules\Doctrine;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\ObjectType;

/**
 * Entities should not expose Doctrine Collections from public methods.
 * Return an array instead to avoid leaking persistence internals.
 *
 * @implements Rule<ClassMethod>
 */
final class NoPublicCollectionReturnRule implements Rule
{
    public function __construct(
        private readonly string $entityNamespaceSegment = 'Entity',
    ) {}

    public function getNodeType(): string
    {
        return ClassMethod::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
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
                    ->identifier('rentbetter.noCollectionReturn')
                    ->build(),
            ];
        }

        return [];
    }

    private function isEntity(ClassReflection $classReflection): bool
    {
        $namespaceParts = explode('\\', $classReflection->getName());
        array_pop($namespaceParts); // remove class name

        return \in_array($this->entityNamespaceSegment, $namespaceParts, true);
    }
}
