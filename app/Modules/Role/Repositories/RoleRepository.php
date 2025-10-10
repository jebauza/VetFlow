<?php

namespace App\Modules\Role\Repositories;

use App\Models\Permission;
use App\Modules\Role\Models\Role;
use App\Modules\Role\Requests\StoreRoleRequest;

class RoleRepository
{
    public function all()
    {
        return Role::all();
    }

    public function find($id): Role
    {
        return Role::findOrFail($id);
    }

    public function create(array $data): Role
    {
        $role = Role::create([
            Role::NAME => $data[StoreRoleRequest::NAME]
        ]);

        $permissions = Permission::whereIn(Permission::ID, $data[StoreRoleRequest::PERMISSION_IDS])->get();
        $role->syncPermissions($permissions);

        return $role;
    }

    public function update(Role $role, array $data): Role
    {
        $role->update($data);
        return $role;
    }

    public function delete(Role $role)
    {
        $role->delete();
    }
}
