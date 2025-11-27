<?php

namespace App\Modules\Permission\Repositories;

use App\Modules\Permission\Models\Permission;
use Illuminate\Database\Eloquent\Collection;

class PermissionRepository
{
    /**
     * Retrieve all permissions.
     *
     * @return Collection
     */
    public function all(): Collection
    {
        return Permission::all();
    }

    /**
     * Find a permission by its ID.
     *
     * @param int $id The ID of the permission.
     * @return Permission
     */
    public function find($id): Permission
    {
        return Permission::findOrFail($id);
    }

    /**
     * Create a new permission.
     *
     * @param array $data The data for the new permission.
     * @return Permission
     */
    public function create(array $data): Permission
    {
        return Permission::create($data);
    }

    /**
     * Delete all permissions.
     *
     * @return void
     */
    public function deleteAll(): void
    {
        Permission::query()->delete();
    }

    /**
     * Retrieve permissions where a given column's value is in a given array.
     *
     * @param string $column The column to check.
     * @param array $values The array of values to check against.
     * @return Collection
     */
    public function whereIn(string $column, array $values): Collection
    {
        return Permission::whereIn($column, $values)->get();
    }

    /**
     * Retrieve permissions where a given column's value is not in a given array.
     *
     * @param string $column The column to check.
     * @param array $values The array of values to check against.
     * @return Collection
     */
    public function whereNotIn(string $column, array $values): Collection
    {
        return Permission::whereNotIn($column, $values)->get();
    }

    /**
     * Assign roles to a specific permission.
     *
     * @param Permission $permission The permission to assign roles to.
     * @param array $roleIds An array of role IDs to assign.
     * @return Permission
     */
    public function assignRoles(Permission $permission, array $roleIds): Permission
    {
        $permission->assignRole($roleIds);

        return $permission->refresh();
    }
}
