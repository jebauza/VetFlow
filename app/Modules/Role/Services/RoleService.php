<?php

namespace App\Modules\Role\Services;

use App\Modules\Role\Models\Role;
use App\Modules\Role\DTOs\RoleDTO;
use Illuminate\Database\Eloquent\Collection;
use App\Modules\Role\Repositories\RoleRepository;

class RoleService
{
    public function __construct(
        protected readonly RoleRepository $roleRepo
    ) {}

    public function all(string $search = null): Collection
    {
        return $this->roleRepo->search($search, true);
    }

    public function findById(string $id): Role
    {
        /** @var Role $role */
        $role = $this->roleRepo->findOrFailWithRelations($id, ['permissions']);

        return $role;
    }

    public function create(RoleDTO $dto): Role
    {
        $role = $this->roleRepo->create($dto->toArray(true));

        $role = $this->roleRepo->assignPermissions($role, $dto->{RoleDTO::PERMISSION_IDS});

        return $this->roleRepo->load($role, ['permissions']);
    }

    public function update(string $id, RoleDTO $dto): Role
    {
        $role = $this->roleRepo->findOrFail($id);
        $role = $this->roleRepo->update($role, $dto->toArray(true));

        $role = $this->roleRepo->syncPermissions(
            $role,
            $dto->{RoleDTO::PERMISSION_IDS}
        );

        return $this->roleRepo->load($role, ['permissions']);
    }

    public function delete(string $id)
    {
        $this->roleRepo->delete($id);
    }
}
