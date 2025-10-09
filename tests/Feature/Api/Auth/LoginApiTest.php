<?php

namespace Tests\Feature\Api\Auth;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LoginApiTest extends TestCase
{
    use RefreshDatabase;

    private $api = 'api/auth/login';

    public function test_login_with_valid_credentials(): void
    {
        $response = $this->postJson($this->api, [
            'email' => env('USER_SUPERADMIN_EMAIL'),
            'password' => env('USER_SUPERADMIN_PASSWORD'),
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'access_token',
                'token_type',
                'expires_at',
            ]);
    }

    public function test_login_with_invalid_credentials(): void
    {
        $response = $this->postJson($this->api, [
            'email' => env('USER_SUPERADMIN_EMAIL'),
            'password' => 'wrongpass',
        ]);

        $response->assertStatus(401)
            ->assertJson(['error' => 'Unauthorised']);
    }
}
