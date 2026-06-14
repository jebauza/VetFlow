<?php

namespace App\Modules\User\Services;

use App\Modules\User\Models\User;
use App\Modules\Role\Models\Role;
use App\Modules\Role\Services\RoleService;
use App\Modules\User\DTOs\CreateUserDTO;
use App\Modules\User\Repositories\UserRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class VeterinaryService
{
    public function __construct(
        protected readonly RoleService $roleService,
        protected readonly UserService $userService,

        protected readonly UserRepository $userRepo
    ) {}

    public function all(string $search = null)
    {
        return $this->userRepo->searchVeterinaries($search, true);
    }

    public function create(CreateUserDTO $dto): User
    {
        $veterinaryRoleIds = $this->roleService->veterinaryRoles()->pluck(Role::ID);

        if (!$veterinaryRoleIds->contains($dto->role_id)) {
            throw new ModelNotFoundException("The role_id '{$dto->role_id}' is not a veterinary role.");
        }

        return $this->userService->create($dto);
    }

    public function isVeterinary(string $userId): bool
    {
        $veterinary = $this->all()->find($userId);

        return $veterinary === null ? false : true;
    }
}
