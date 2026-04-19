<?php

// Stubs — keep this self-contained so the test doesn't need doctrine/dbal installed.
namespace Doctrine\ORM {
    interface EntityManagerInterface {}
    class EntityManager implements EntityManagerInterface {}
}

namespace Doctrine\DBAL {
    class Connection {}
}

namespace App\Repository {
    use Doctrine\DBAL\Connection;
    use Doctrine\ORM\EntityManagerInterface;

    class FooRepository
    {
        public function __construct(
            private readonly EntityManagerInterface $em, // OK — repositories own DB access
            private readonly Connection $conn,           // OK
        ) {}
    }

    class BarRepository
    {
        public function __construct(
            private readonly EntityManagerInterface $em,
        ) {}
    }
}

namespace App\Service {
    use Doctrine\DBAL\Connection;
    use Doctrine\ORM\EntityManagerInterface;
    use App\Repository\BarRepository;

    class BadService
    {
        public function __construct(
            private readonly EntityManagerInterface $em, // ERROR — service→dbAccess
            private readonly Connection $conn,           // ERROR — service→dbAccess
        ) {}
    }

    class GoodService
    {
        public function __construct(
            private readonly BarRepository $repo, // OK — repository allowed in service
        ) {}
    }

    class FooService {}
}

namespace App\Controller {
    use App\Repository\FooRepository;
    use App\Service\FooService;
    use Doctrine\DBAL\Connection;
    use Doctrine\ORM\EntityManagerInterface;

    class BadController
    {
        public function __construct(
            private readonly EntityManagerInterface $em, // ERROR — controller→dbAccess
            private readonly Connection $conn,           // ERROR — controller→dbAccess
            private readonly FooRepository $repo,        // ERROR — controller→repository
        ) {}
    }

    class GoodController
    {
        public function __construct(
            private readonly FooService $service, // OK — services allowed in controllers
        ) {}
    }

    class NoConstructorController {}
}

namespace App\Helper {
    use Doctrine\DBAL\Connection;

    class BadHelper
    {
        public function __construct(
            private readonly Connection $conn, // ERROR — helper matches service group
        ) {}
    }
}

namespace App\Other {
    use Doctrine\ORM\EntityManagerInterface;

    class NotInAnyGroup
    {
        public function __construct(
            private readonly EntityManagerInterface $em, // OK — not in any from-group
        ) {}
    }
}
