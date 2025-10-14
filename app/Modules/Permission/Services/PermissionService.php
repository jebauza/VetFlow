<?php

namespace App\Modules\Permission\Services;

use App\Modules\Permission\Models\Permission;
use App\Modules\Permission\Repositories\PermissionRepository;

class PermissionService
{
    protected PermissionRepository $repo;

    public function __construct(PermissionRepository $repo)
    {
        $this->repo = $repo;
    }

    public function getAllPermissions()
    {
        return $this->repo->all();
    }

    public function getPermissions()
    {
        return $this->getAllPermissions()->whereNotIn(Permission::NAME, [
            Permission::NAME_SUPERADMIN,
        ]);
    }

    public function getPermissionById($id): Permission
    {
        return $this->repo->find($id);
    }
}
