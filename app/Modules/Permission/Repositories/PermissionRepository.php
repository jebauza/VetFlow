<?php

namespace App\Modules\Permission\Repositories;

use App\Modules\Permission\Models\Permission;

class PermissionRepository
{
    public function all()
    {
        return Permission::all();
    }

    public function find($id): Permission
    {
        return Permission::findOrFail($id);
    }

    public function getValidIds(array $ids)
    {
        return Permission::whereIn(Permission::ID, $ids)->pluck(Permission::ID);
    }
}
