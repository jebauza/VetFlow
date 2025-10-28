<?php

namespace App\Modules\Role\Repositories;

use App\Modules\Role\Models\Role;
use Illuminate\Database\Eloquent\Collection;

class RoleRepository
{
    /**
     * Create a new RoleRepository instance.
     *
     * @param  // protected readonly PermissionRepository $permissionRepo
     */
    public function __construct(
        // protected readonly PermissionRepository $permissionRepo
    ) {}

    /**
     * Get all roles ordered by name.
     *
     * @return Collection<int, Role>
     */
    public function all(): Collection
    {
        return Role::orderBy(Role::NAME)
            ->get();
    }

    /**
     * Get roles by a search query, ordered alphabetically by name.
     *
     * @param string|null $search The search string.
     * @return Collection<int, Role>
     */
    public function getBySearch(?string $search): Collection
    {
        return Role::search($search)
            ->orderByRaw('LOWER(' . Role::NAME . ') ASC')
            ->get();
    }

    /**
     * Find a role by its ID.
     *
     * @param int $id The ID of the role.
     * @return Role
     */
    public function find($id): Role
    {
        return Role::findOrFail($id);
    }

    /**
     * Create a new role.
     *
     * @param array<string, mixed> $data The data for the new role.
     * @return Role
     */
    public function create(array $data): Role
    {
        return Role::create($data);
    }

    /**
     * Update an existing role.
     *
     * @param Role $role The role instance to update.
     * @param array<string, mixed> $data The data to update the role with.
     * @return Role
     */
    public function update(Role $role, array $data): Role
    {
        $role->update($data);

        return $role;
    }

    /**
     * Delete a role.
     *
     * @param Role $role The role instance to delete.
     * @return void
     */
    public function delete(Role $role): void
    {
        $role->delete();
    }

    /**
     * Delete all roles.
     *
     * @return void
     */
    public function deleteAll(): void
    {
        Role::query()->delete();
    }

    /**
     * Sync permissions to a role.
     *
     * @param Role $role The role instance to sync permissions to.
     * @param array<int, int> $permissionIds An array of permission IDs.
     * @return Role
     */
    public function syncPermissionIdsToRole(Role $role, array $permissionIds): Role
    {
        $role->syncPermissions($permissionIds);

        return $role->refresh();
    }

    /**
     * Get roles where a given column's value is in a given array.
     *
     * @param string $column The column name.
     * @param array<int, mixed> $values The array of values to check against.
     * @return Collection<int, Role>
     */
    public function whereIn(string $column, array $values): Collection
    {
        return Role::whereIn($column, $values)->get();
    }

    /**
     * Get roles where a given column's value is not in a given array.
     *
     * @param string $column The column name.
     * @param array<int, mixed> $values The array of values to check against.
     * @return Collection<int, Role>
     */
    public function whereNotIn(string $column, array $values): Collection
    {
        return Role::whereNotIn($column, $values)->get();
    }
}
