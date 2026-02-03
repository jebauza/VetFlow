<?php

namespace App\Modules\Auth\Services;

use App\Modules\Role\Models\Role;
use App\Modules\User\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Modules\Auth\DTOs\AuthTokenDTO;
use App\Modules\User\DTOs\CreateUserDTO;
use App\Modules\User\Services\UserService;
use App\Modules\Permission\Models\Permission;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use App\Modules\User\Repositories\UserRepository;

class AuthService
{
    public function __construct(
        protected readonly UserService $userService
    ) {}

    public function register1(CreateUserDTO $createUserDTO): User
    {
        $user = $this->userService->create($createUserDTO);

        return $user;
    }

    public function register(CreateUserDTO $createUserDTO): AuthTokenDTO
    {
        $user = $this->userService->create($createUserDTO);
        $token = JWTAuth::fromUser($user);

        return new AuthTokenDTO($token, $user);
    }

    public function me(): array
    {
        $user = Auth::user();
        $data = $user->only(
            User::ID,
            User::EMAIL,
            User::NAME,
            User::SURNAME,
            User::AVATAR,
        );
        $data['permissions'] = $this->userRepo->getAllPermissions($user)->pluck(Permission::NAME);
        $data['roles'] = $this->userRepo->getRoles($user)->pluck(Role::NAME);

        return $data;
    }
}
