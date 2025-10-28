<?php

namespace Tests;

use App\Modules\User\Models\User;
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

    /**
     * Helper to find the most repeated substring across an array of single-word names
     *
     * @param array $strings
     * @param int $length Substring length
     * @return array ['substring' => string, 'repetitions' => int]
     */
    protected function getMostRepeatedSubstring(array $strings, int $length = 1): array
    {
        $strings = array_map('strtolower', $strings);
        $totalRoles = count($strings);
        $counter = [];

        foreach ($strings as $string) {
            $substringsSet = [];

            $len = strlen($string);
            for ($i = 0; $i <= $len - $length; $i++) {
                $sub = substr($string, $i, $length);
                $substringsSet[$sub] = true; // count only once per name
            }

            foreach ($substringsSet as $sub => $_) {
                $counter[$sub] = ($counter[$sub] ?? 0) + 1;
            }
        }

        // Exclude substrings that appear in all roles
        $filtered = array_filter($counter, fn($count) => $count < $totalRoles);

        if (empty($filtered)) {
            // fallback if all substrings are universal
            return ['substring' => '', 'repetitions' => 0];
        }

        arsort($filtered);

        return [
            'substring' => array_key_first($filtered),
            'repetitions' => current($filtered),
        ];
    }
}
