<?php

namespace App\Modules\Auth\Services;

use App\Modules\Role\Models\Role;
use App\Modules\User\Models\User;
use App\Modules\User\DTOs\UserDTO;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Modules\Permission\Models\Permission;
use App\Modules\User\Repositories\UserRepository;

class AuthService
{
    public function __construct(
        protected readonly UserRepository $userRepo
    ) {}

    public function register(UserDTO $userDTO): User
    {
        $userDTO->{UserDTO::PASSWORD} = Hash::make($userDTO->{UserDTO::PASSWORD});
        $user = $this->userRepo->store($userDTO);

        return $user;
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
