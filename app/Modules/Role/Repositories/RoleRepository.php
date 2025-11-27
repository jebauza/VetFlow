<?php

namespace App\Modules\Role\Repositories;

use App\Modules\Role\Models\Role;
use App\Common\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Collection;

class RoleRepository extends BaseRepository
{
    public function __construct(Role $model)
    {
        parent::__construct($model);
    }

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
}
