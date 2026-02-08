<?php

namespace App\Modules\Role\Repositories;

use App\Modules\Role\Models\Role;
use Illuminate\Database\Eloquent\Builder;
use App\Common\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Collection;
use App\Modules\Permission\Models\Permission;

class RoleRepository extends BaseRepository
{
    public function __construct(Role $model)
    {
        parent::__construct($model);
    }

    public function queryAll(): Builder
    {
        return Role::query()->orderBy(Role::NAME);
    }

    public function baseSearch(?string $search = null, bool|array $relations = false): Builder
    {
        return Role::search($search)
            ->with('permissions:' . Permission::ID . ',' . Permission::NAME)
            ->when($relations, function (Builder $q) use ($relations) {
                if (is_array($relations)) {
                    $q->with($relations);
                } else {

                    $q->with([
                        'permissions:' . Permission::ID . ',' . Permission::NAME,
                    ]);
                }
            })
            ->orderByRaw('LOWER(' . Role::NAME . ') ASC');
    }

    public function search(?string $search, bool|array $relations = false): Collection
    {
        return $this->baseSearch($search, $relations)->get();
    }

    public function searchCount(?string $search, bool|array $relations = false): int
    {
        return $this->baseSearch($search, $relations)->count();
    }

    public function queryBySearch(?string $search = null): Builder
    {
        return Role::search($search)
            ->with('permissions:' . Permission::ID . ',' . Permission::NAME)
            ->orderByRaw('LOWER(' . Role::NAME . ') ASC');
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
     * Assign permissions to a role.
     *
     * @param Role|string $role The role instance or role ID to assign permissions to.
     * @param array<int, int> $permissionIds An array of permission IDs.
     * @return Role
     */
    public function assignPermissions(Role|string $role, array $permissionIds): Role
    {
        if (is_string($role)) {
            $role = $this->findOrFail($role);
        }

        $role->givePermissionTo($permissionIds);

        return $role;
    }

    /**
     * Sync permissions to a role.
     *
     * @param Role|string $role The role instance or role ID to sync permissions to.
     * @param array<int, int> $permissionIds An array of permission IDs.
     * @return Role
     */
    public function syncPermissions(Role|string $role, array $permissionIds): Role
    {
        if (is_string($role)) {
            $role = $this->findOrFail($role);
        }

        $role->syncPermissions($permissionIds);

        return $role;
    }
}
