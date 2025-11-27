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
                $roleRepo->syncPermissionIdsToRole($mRole, $permissionIds);
            }
        }
        $allRoles = $roleRepo->all();


        // Create users
        $configUsers = config('vetflow.users');
        $userRepository = app(UserRepository::class);
        $userRepository->deleteAllExcept(User::EMAIL, array_column($configUsers, 'email'));
        foreach ($configUsers as $user) {
            if (!empty($user['email'])) {
                $userRepository->upsert(
                    $user['name'],
                    $user['name'],
                    $user['email'],
                    $user['password'],
                    isset($user['superadmin']) ? $user['superadmin'] : false
                );

                $mUser = $userRepository->findOneBy(User::EMAIL, $user['email']);

                if (!empty($user['permissions']) && $mUser) {
                    $permissionIds = $allPermissions->whereIn(Permission::NAME, $user['permissions'])
                        ->pluck(Permission::ID)
                        ->toArray();
                    $userRepository->syncPermissionIdsToUser($mUser, $permissionIds);
                }

                if (!empty($user['roles']) && $mUser) {
                    $roleIds = $allRoles->whereIn(Role::NAME, $user['roles'])
                        ->pluck(Role::ID)
                        ->toArray();
                    $userRepository->syncRoleIdsToUser($mUser, $roleIds);
                }
            }
        }

        $this->command->info(self::class . ' is finished');
    }
}
