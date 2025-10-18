<?php

namespace App\Modules\Role\Repositories;

use App\Modules\Role\Models\Role;
use Illuminate\Database\Eloquent\Collection;
use App\Modules\Permission\Models\Permission;

class RoleRepository
{
    public function all(): Collection
    {
        return Role::orderBy(Role::NAME)
            ->get();
    }

    public function getBySearch(?string $search): Collection
    {
        return Role::search($search)
            ->orderByRaw('LOWER(' . Role::NAME . ') ASC')
            ->get();
    }

    public function find($id): Role
    {
        return Role::findOrFail($id);
    }

    public function create(array $data): Role
    {
        return Role::create($data);
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

    private function syncPermissionIdsToRole(Role $role, array $permissionIds): Role
    {
        $permissions = Permission::whereIn(Permission::ID, $permissionIds)->get();
        $role->syncPermissions($permissions);

        return $role;
    }
}
