<?php

namespace App\Modules\Role\Services;

use App\Modules\Role\Models\Role;
use App\Modules\Role\DTOs\RoleDTO;
use App\Modules\Role\Repositories\RoleRepository;

class RoleService
{
    protected RoleRepository $repo;

    public function __construct(RoleRepository $repo)
    {
        $this->repo = $repo;
    }

    public function getAllRoles()
    {
        return $this->repo->all();
    }

    public function getRoleById($id): Role
    {
        return $this->repo->find($id);
    }

    public function createRole(RoleDTO $dto): Role
    {
        $role = $this->repo->create([
            Role::NAME => $dto->{RoleDTO::NAME},
        ]);

        $role->syncPermissions($dto->{RoleDTO::PERMISSION_IDS});

        return $role->load('permissions');
    }

    public function updateRole(Role $role, RoleDTO $dto): Role
    {
        $role = $this->repo->update($role, [
            Role::NAME => $dto->{RoleDTO::NAME},
        ]);

        $role->syncPermissions($dto->{RoleDTO::PERMISSION_IDS});

        return $role->load('permissions');
    }

    public function deleteRole(Role $role)
    {
        $this->repo->delete($role);
    }
}
