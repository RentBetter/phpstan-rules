<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;

class BadController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {}
}

class GoodController
{
    public function __construct() {}
}

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;

class SomeHelper
{
    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {}
}
