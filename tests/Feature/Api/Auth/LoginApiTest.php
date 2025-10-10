<?php

namespace Tests\Feature\Api\Auth;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LoginApiTest extends TestCase
{
    use RefreshDatabase;

    private $api = 'api/auth/login';

    public function test_login(): void
    {
        $response = $this->postJson($this->api, [
            'email' => $this->superAdminEmail(),
            'password' => $this->superAdminPassword(),
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
            'email' => $this->superAdminEmail(),
            'password' => 'wrongpass',
        ]);

        $response->assertStatus(401)
            ->assertJson(['message' => 'Unauthorized']);
    }

    public function test_login_validation_with_invalid_data(): void
    {
        $response = $this->postJson($this->api, [
            'email' => 'gleichner.erick',
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure(['email', 'password']);
    }
}
