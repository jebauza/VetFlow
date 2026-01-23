<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Modules\Role\Models\Role;
use App\Modules\User\Models\User;
use App\Modules\Permission\Models\Permission;
use App\Modules\Role\Repositories\RoleRepository;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Modules\Permission\Repositories\PermissionRepository;
use App\Modules\User\Repositories\UserRepository;

class UserFakeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissionRepo = app(PermissionRepository::class);
        $roleRepo = app(RoleRepository::class);
        $userRepo = app(UserRepository::class);
        $chunkUser = 10;

        $roles = $roleRepo->whereIn(Role::NAME, [
            Role::ADMIN_NAME,
            Role::VET_NAME,
            Role::ASSISTANT_NAME,
            Role::RECEPTIONIST_NAME
        ]);
        $rolesCount = $roles->count();
        $permissions = Permission::whereNotIn(Permission::NAME, [Permission::NAME_SUPERADMIN])
            ->with('roles')
            ->get();
        $users = User::factory($rolesCount * $chunkUser)->create();

        $users->chunk($chunkUser)->each(function ($chunkOfUsers, $index) use ($userRepo, $roles) {
            $chunkOfUsers->each(function (User $user) use ($userRepo, $roles, $index) {
                $userRepo->assignRoles($user, [$roles[$index]->{Role::ID}]);
            });
        });

        $permissions->chunk($permissions->count() / $rolesCount)->each(function ($chunkOfPermissions, $index) use ($permissionRepo, $roles) {
            $chunkOfPermissions->each(function (Permission $permission) use ($permissionRepo, $roles, $index) {
                $permissionRepo->assignRoles($permission, [$roles[$index]->{Role::ID}]);
            });
        });

        $this->command->info(self::class . ' is finished');
    }
}
