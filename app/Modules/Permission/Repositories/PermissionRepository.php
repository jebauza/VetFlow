<?php

namespace App\Modules\Permission\Repositories;

use Illuminate\Database\Eloquent\Builder;
use App\Common\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Collection;
use App\Modules\Permission\Models\Permission;

class PermissionRepository extends BaseRepository
{
    public function __construct(Permission $model)
    {
        parent::__construct($model);
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
