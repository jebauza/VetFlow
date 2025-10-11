<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Permission;
use Illuminate\Database\Seeder;
use App\Modules\Role\Models\Role;
use App\Common\Helpers\UuidHelper;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\PermissionRegistrar;

class UserRolePermissionSeeder extends Seeder
{
    /**
     * Create the initial roles and permissions.
     */
    public function run(): void
    {
        $configPermissions = config('vetflow.permissions');
        $configRoles = config('vetflow.roles');
        $configUsers = config('vetflow.users');

        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        Permission::query()->delete();
        foreach ($configPermissions as $permission) {
            Permission::create(['name' => $permission]);
        }
        $allPermissions = Permission::get();

        // Create roles
        Role::query()->delete();
        foreach ($configRoles as $role) {
            $mRole = Role::create(['name' => $role['name']]);

            if (!empty($role['permissions']) && $mRole) {
                $permissions = $allPermissions->whereIn(Permission::NAME, $role['permissions']);
                $mRole->syncPermissions($permissions);
            }
        }
        $allRoles = Role::get();

        // Create users
        User::whereNotIn(User::EMAIL, array_column($configUsers, 'email'))->delete();
        foreach ($configUsers as $user) {
            if (!empty($user['email'])) {
                User::upsert(
                    [
                        User::ID => UuidHelper::newBinaryUuid(),
                        User::NAME => $user['name'],
                        User::SURNAME => $user['name'],
                        User::EMAIL => $user['email'],
                        User::EMAIL_VERIFIED_AT => now(),
                        User::PASSWORD => Hash::make($user['password']),
                    ],
                    User::EMAIL,
                    [
                        User::NAME,
                        User::PASSWORD,
                    ]
                );

                $mUser = User::firstWhere(User::EMAIL, $user['email']);

                if (!empty($user['permissions']) && $mUser) {
                    $permissions = $allPermissions->whereIn(Permission::NAME, $user['permissions']);
                    $mUser->syncPermissions($permissions);
                }

                if (!empty($user['roles']) && $mUser) {
                    $roles = $allRoles->whereIn(Role::NAME, $user['roles']);
                    $mUser->syncRoles($roles);
                }
            }
        }

        $this->command->info(self::class . ' is finished');
    }
}
