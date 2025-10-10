<?php

namespace App\Modules\Role\Services;

use App\Modules\Role\Repositories\RoleRepository;
use App\Modules\Role\Models\Role;

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

    public function createRole(array $data): Role
    {
        return $this->repo->create($data);
    }

    public function updateRole(Role $role, array $data): Role
    {
        return $this->repo->update($role, $data);
    }

    public function deleteRole(Role $role)
    {
        $this->repo->delete($role);
    }
}
