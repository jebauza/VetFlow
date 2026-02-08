<?php

namespace App\Modules\Auth\DTOs;

use App\Modules\User\Models\User;

class AuthTokenDTO
{
    public function __construct(
        public readonly string $token,
        public readonly User $user
    ) {}
}
