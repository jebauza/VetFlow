<?php

namespace Tests;

use App\Models\User;
use Database\Seeders\UserRolePermissionSeeder;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(UserRolePermissionSeeder::class);
    }

    protected function superAdminEmail(): string
    {
        return env('USER_SUPERADMIN_EMAIL');
    }

    protected function superAdminPassword(): string
    {
        return env('USER_SUPERADMIN_PASSWORD');
    }

    protected function superAdmin(): User
    {
        return User::firstWhere(User::EMAIL, $this->superAdminEmail());
    }

    protected function getAccessToken(User $user): string
    {
        return JWTAuth::fromUser($user);
    }
}
