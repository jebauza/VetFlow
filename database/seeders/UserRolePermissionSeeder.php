<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Modules\Role\Models\Role;
use App\Modules\User\Models\User;
use App\Modules\User\Repositories\UserRepository;
use Spatie\Permission\PermissionRegistrar;
use App\Modules\Permission\Models\Permission;
use App\Modules\Role\Repositories\RoleRepository;
use App\Modules\Permission\Repositories\PermissionRepository;

class UserRolePermissionSeeder extends Seeder
{
    /**
     * Create the initial roles and permissions.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
        $permissionRepo = app(PermissionRepository::class);

        // Create permissions
        $configPermissions = config('vetflow.permissions');
        $permissionRepo = app(PermissionRepository::class);
        $permissionRepo->deleteAll();
        foreach ($configPermissions as $permission) {
            $permissionRepo->create([Permission::NAME => $permission]);
        }
        $allPermissions = $permissionRepo->all();


        // Create roles
        $configRoles = config('vetflow.roles');
        $roleRepo = app(RoleRepository::class);
        $roleRepo->deleteAll();
        foreach ($configRoles as $role) {
            $mRole = $roleRepo->create([Role::NAME => $role['name']]);

            if (!empty($role['permissions']) && $mRole) {
                $permissionIds = $allPermissions->whereIn(Permission::NAME, $role['permissions'])
                    ->pluck(Permission::ID)
                    ->toArray();
                $roleRepo->assignPermissions($mRole, $permissionIds);
            }
        }
        $allRoles = $roleRepo->all();


        // // Create users
        $configUsers = config('vetflow.users');
        $userRepo = app(UserRepository::class);
        $userRepo->deleteAllExcept(User::EMAIL, array_column($configUsers, 'email'));
        foreach ($configUsers as $user) {
            if (!empty($user['email'])) {
                $userRepo->upsert(
                    $user['name'],
                    $user['name'],
                    $user['email'],
                    $user['password'],
                    isset($user['superadmin']) ? $user['superadmin'] : false
                );

                $mUser = $userRepo->findOneBy(User::EMAIL, $user['email']);

                if (!empty($user['permissions']) && $mUser) {
                    $permissionIds = $allPermissions->whereIn(Permission::NAME, $user['permissions'])
                        ->pluck(Permission::ID)
                        ->toArray();
                    $userRepo->assignPermissions($mUser, $permissionIds);
                }

                if (!empty($user['roles']) && $mUser) {
                    $roleIds = $allRoles->whereIn(Role::NAME, $user['roles'])
                        ->pluck(Role::ID)
                        ->toArray();
                    $userRepo->assignRoles($mUser, $roleIds);
                }
            }
        }

        $this->command->info(self::class . ' is finished');
    }
}
