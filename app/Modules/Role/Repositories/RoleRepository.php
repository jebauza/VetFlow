<?php

namespace App\Modules\Role\Repositories;

use App\Modules\Role\Models\Role;

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
}
