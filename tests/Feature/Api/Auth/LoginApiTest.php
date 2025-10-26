<?php

namespace Tests\Feature\Api\Auth;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LoginApiTest extends TestCase
{
    use RefreshDatabase;

    private $api = 'api/auth/login';

    public function test_login_200(): void
    {
        $this->postJson($this->api, [
            'email' => $this->superAdminEmail(),
            'password' => $this->superAdminPassword(),
        ])
            ->assertStatus(200)
            ->assertJsonStructure([
                'access_token',
                'token_type',
                'expires_at',
            ]);
    }

    public function test_login_with_invalid_credentials_401(): void
    {
        $this->postJson($this->api, [
            'email' => $this->superAdminEmail(),
            'password' => 'wrongpass',
        ])
            ->assertStatus(401)
            ->assertJson(['message' => 'Unauthorized']);
    }

    public function test_login_validation_with_invalid_data_422(): void
    {
        $this->postJson($this->api, [
            'email' => 'gleichner.erick',
        ])
            ->assertStatus(422)
            ->assertJsonStructure(['email', 'password']);
    }
}
