<?php

namespace App\Modules\Role\Repositories;

use App\Models\Permission;
use App\Modules\Role\Models\Role;
use App\Modules\Role\Requests\StoreRoleRequest;
use App\Modules\Role\Requests\UpdateRoleRequest;

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

        return $this->syncPermissionIdsToRole($role, $data[StoreRoleRequest::PERMISSION_IDS]);
    }

    public function update(Role $role, array $data): Role
    {
        $role->update([
            Role::NAME => $data[UpdateRoleRequest::NAME]
        ]);

        return $this->syncPermissionIdsToRole($role, $data[UpdateRoleRequest::PERMISSION_IDS]);
    }

    public function delete(Role $role)
    {
        $role->delete();
    }

    private function syncPermissionIdsToRole(Role $role, array $permissionIds): Role
    {
        $permissions = Permission::whereIn(Permission::ID, $permissionIds)->get();
        $role->syncPermissions($permissions);

        return $role;
    }
}
