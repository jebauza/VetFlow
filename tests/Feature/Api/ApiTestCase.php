<?php

namespace Tests\Feature\Api;

use App\Modules\User\Models\User;
use Database\Seeders\UserRolePermissionSeeder;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class ApiTestCase extends BaseTestCase
{
    const GET = 'get';
    const POST = 'post';
    const PUT = 'put';
    const DELETE = 'delete';

    protected User $superAdmin;

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
        $this->superAdmin ??= User::firstWhere(User::EMAIL, $this->superAdminEmail());
        return $this->superAdmin;
    }

    protected function getAccessToken(User $user): string
    {
        return JWTAuth::fromUser($user);
    }

    protected function assertEndpointRequiresAuth(string $method, string $api, array $data = []): void
    {
        $this->json(strtoupper($method), $api, $data, [
            'Authorization' => 'Bearer invalid_token',
        ])
            ->assertStatus(401)
            ->assertJson([
                'message' => __('Unauthorized'),
                'errors' => [
                    'auth' => [__('Authentication token is invalid or expired')],
                ],
            ]);
    }

    protected function assertEndpointReturnsNotFound(string $method, string $api, string $token = '', array $data = []): void
    {
        $this->json(strtoupper($method), $api, $data, [
            'Authorization' => "Bearer {$token}",
        ])
            ->assertNotFound()
            ->assertJsonStructure(['message']);
    }

    protected function assertEndpointReturnsForbidden(string $method, string $api, string $token = null, array $data = []): void
    {
        $token = $token ?? $this->getAccessToken(User::factory()->create());

        $this->json(strtoupper($method), $api, $data, [
            'Authorization' => "Bearer {$token}",
        ])
            ->assertForbidden()
            ->assertJson([
                'message' => __('You do not have permission to access this resource'),
            ]);
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
