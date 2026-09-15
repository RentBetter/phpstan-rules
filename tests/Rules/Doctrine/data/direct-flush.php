<?php

namespace App\Service {

    use Doctrine\ORM\EntityManagerInterface;

    class FlushService
    {
        public function __construct(
            private readonly EntityManagerInterface $em,
        ) {}

        public function bad(): void
        {
            $this->em->flush(); // ERROR
        }

        public function ok(): void
        {
            $this->em->persist(new \stdClass());  // OK - not flush
        }
    }
}

namespace App\Repository {

    use Doctrine\ORM\EntityManagerInterface;

    trait SaveTrait
    {
        public function save(object $entity, bool $flush = true): void
        {
            $this->em->persist($entity);
            if ($flush) {
                $this->em->flush(); // OK - a repository trait, judged as its using repository
            }
        }
    }

    class TenancyRepository
    {
        use SaveTrait;

        public function __construct(
            private readonly EntityManagerInterface $em,
        ) {}

        public function remove(object $entity): void
        {
            $this->em->remove($entity);
            $this->em->flush(); // OK - repositories own persistence
        }
    }
}
