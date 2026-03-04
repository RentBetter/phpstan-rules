<?php

namespace App\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;

class ThingRepository
{
    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {}

    // --- ERROR cases ---

    public function hardcodedInClause(): void
    {
        $this->em->createQuery('SELECT t FROM Thing t WHERE t.status IN (0, 2, 3)'); // ERROR line 19
    }

    public function hardcodedEquality(): void
    {
        $qb = $this->em->createQueryBuilder();
        $qb->andWhere('t.status = 0'); // ERROR line 25
    }

    public function hardcodedNotEqual(): void
    {
        $qb = $this->em->createQueryBuilder();
        $qb->andWhere('t.status != 2'); // ERROR line 31
    }

    public function hardcodedDiamondNotEqual(): void
    {
        $qb = $this->em->createQueryBuilder();
        $qb->orWhere('t.type <> 3'); // ERROR line 37
    }

    public function hardcodedInWhere(): void
    {
        $qb = $this->em->createQueryBuilder();
        $qb->where('t.type IN (1, 2)'); // ERROR line 43
    }

    public function hardcodedNotIn(): void
    {
        $qb = $this->em->createQueryBuilder();
        $qb->orWhere('t.type NOT IN (0, 1, 2)'); // ERROR line 49
    }

    public function hardcodedInHaving(): void
    {
        $qb = $this->em->createQueryBuilder();
        $qb->having('t.status = 5'); // ERROR line 55
    }

    public function hardcodedInSetDQL(): void
    {
        $query = $this->em->createQuery('SELECT t FROM Thing t');
        $query->setDQL('SELECT t FROM Thing t WHERE t.status IN (0, 1)'); // ERROR line 61
    }

    public function hardcodedNegativeValue(): void
    {
        $qb = $this->em->createQueryBuilder();
        $qb->andWhere('t.status = -1'); // ERROR line 67
    }

    // --- OK cases ---

    public function parameterInClause(): void
    {
        $this->em->createQuery('SELECT t FROM Thing t WHERE t.status IN (:statuses)'); // OK
    }

    public function parameterComparison(): void
    {
        $qb = $this->em->createQueryBuilder();
        $qb->andWhere('t.status = :status'); // OK
    }

    public function positionalParameter(): void
    {
        $qb = $this->em->createQueryBuilder();
        $qb->andWhere('t.status = ?1'); // OK
    }

    public function noNumericLiterals(): void
    {
        $qb = $this->em->createQueryBuilder();
        $qb->andWhere('t.name IS NOT NULL'); // OK
    }

    public function stringComparison(): void
    {
        $qb = $this->em->createQueryBuilder();
        $qb->andWhere("t.name = 'active'"); // OK
    }

    public function notADqlMethod(): void
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('t.status'); // OK — select is not a DQL condition method
    }

    public function concatWithEnumValue(): void
    {
        $qb = $this->em->createQueryBuilder();
        $qb->andWhere('t.status = ' . 0); // OK — concat, not a plain string literal
    }

    public function functionCallComparison(): void
    {
        $qb = $this->em->createQueryBuilder();
        $qb->andWhere('SIZE(t.items) = 0'); // OK — SIZE(...) doesn't match dotted identifier pattern
    }

    // --- expr() ERROR cases ---

    public function exprEqWithInt(): void
    {
        $qb = $this->em->createQueryBuilder();
        $qb->andWhere($qb->expr()->eq('t.status', 0)); // ERROR — hardcoded int
    }

    public function exprNeqWithInt(): void
    {
        $qb = $this->em->createQueryBuilder();
        $qb->andWhere($qb->expr()->neq('t.status', 2)); // ERROR — hardcoded int
    }

    public function exprEqWithString(): void
    {
        $qb = $this->em->createQueryBuilder();
        $qb->andWhere($qb->expr()->eq('t.status', 'draft')); // ERROR — hardcoded string
    }

    public function exprInWithIntArray(): void
    {
        $qb = $this->em->createQueryBuilder();
        $qb->andWhere($qb->expr()->in('t.status', [0, 2, 3])); // ERROR — hardcoded int array
    }

    public function exprNotInWithStringArray(): void
    {
        $qb = $this->em->createQueryBuilder();
        $qb->andWhere($qb->expr()->notIn('t.status', ['draft', 'archived'])); // ERROR — hardcoded string array
    }

    public function exprInWithMixedArray(): void
    {
        $qb = $this->em->createQueryBuilder();
        $qb->andWhere($qb->expr()->in('t.status', [0, 'draft'])); // ERROR — hardcoded mixed array
    }

    // --- expr() OK cases ---

    public function exprEqWithParam(): void
    {
        $qb = $this->em->createQueryBuilder();
        $qb->andWhere($qb->expr()->eq('t.status', ':status')); // OK — parameter
    }

    public function exprInWithParam(): void
    {
        $qb = $this->em->createQueryBuilder();
        $qb->andWhere($qb->expr()->in('t.status', ':statuses')); // OK — parameter string
    }

    public function exprEqWithVariable(): void
    {
        $qb = $this->em->createQueryBuilder();
        $value = 0;
        $qb->andWhere($qb->expr()->eq('t.status', $value)); // OK — variable
    }

    public function exprEqWithPositionalParam(): void
    {
        $qb = $this->em->createQueryBuilder();
        $qb->andWhere($qb->expr()->eq('t.status', '?1')); // OK — positional parameter
    }
}
