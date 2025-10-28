<?php

namespace App\Modules\User\Services;

use App\Modules\User\Repositories\UserRepository;

class UserService
{
    public function __construct(
        protected readonly UserRepository $userRepo
    ) {}
}
