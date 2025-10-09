<?php

namespace Tests;

use App\Models\User;
use Database\Seeders\UserRolePermissionSeeder;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(UserRolePermissionSeeder::class);
    }

    public function userEmail(): string
    {
        return env('USER_SUPERADMIN_EMAIL');
    }

    protected function superAdmin(): User
    {
        return User::firstWhere(User::EMAIL, $this->userEmail());
    }
}
