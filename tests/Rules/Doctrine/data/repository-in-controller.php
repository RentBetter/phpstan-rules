<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Service\UserService;

class BadRepoController
{
    public function __construct(
        private readonly UserRepository $userRepository, // ERROR
        private readonly UserService $userService,
    ) {}
}

class GoodRepoController
{
    public function __construct(
        private readonly UserService $userService,
    ) {}
}
