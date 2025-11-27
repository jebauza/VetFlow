<?php

namespace App\Modules\Role\Services;

use App\Modules\Role\Models\Role;
use App\Modules\Role\DTOs\RoleDTO;
use App\Modules\Role\Repositories\RoleRepository;

class RoleService
{
    public function __construct(
        protected readonly RoleRepository $roleRepo
    ) {}

    public function getRoles(string $search = null)
    {
        return $this->roleRepo->getBySearch($search);
    }

    public function getRoleById(string $id): Role
    {
        return $this->roleRepo->findOrFail($id);
    }

    public function createRole(RoleDTO $roleDTO): Role
    {
        $role = $this->roleRepo->create($roleDTO->toArray(true));

        $role->syncPermissions($roleDTO->{RoleDTO::PERMISSION_IDS});

        return $this->roleRepo->load($role, ['permissions']);
    }

    public function updateRole(string $id, RoleDTO $roleDTO): Role
    {
        $role = $this->roleRepo->update($id, $roleDTO->toArray(true));

        $role->syncPermissions($roleDTO->{RoleDTO::PERMISSION_IDS});

        return $this->roleRepo->load($role, ['permissions']);
    }

    public function deleteRole(string $id)
    {
        $this->roleRepo->delete($id);
    }
}
