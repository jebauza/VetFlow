<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserFakeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $chunkUser = 10;

        $roles = Role::whereIn(Role::NAME, [
            Role::ADMIN_NAME,
            Role::VET_NAME,
            Role::ASSISTANT_NAME,
            Role::RECEPTIONIST_NAME
        ])->get();
        $permissions = Permission::whereIn(Permission::NAME, [])->get();
        $rolesCount = $roles->count();

        $users = User::factory($rolesCount * $chunkUser)->create();

        $users->chunk($chunkUser)->each(function ($chunkOfUsers, $index) use ($roles) {
            $chunkOfUsers->each(function (User $user) use ($roles, $index) {
                $user->assignRole($roles[$index]);
            });
        });

        $this->command->info(self::class . ' is finished');
    }
}
