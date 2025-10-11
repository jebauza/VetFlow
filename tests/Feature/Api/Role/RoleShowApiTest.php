<?php

namespace Tests\Feature\Api\Auth;

use Tests\TestCase;
use Illuminate\Support\Str;
use App\Modules\Role\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RoleShowApiTest extends TestCase
{
    use RefreshDatabase;

    private $api = 'api/roles/:id';

    public function test_show_200()
    {
        $user = $this->superAdmin();
        $token = $this->getAccessToken($user);
        $role = Role::inRandomOrder()->first();

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$token}",
        ])->getJson(str_replace(':id', $role->{Role::ID}, $this->api));

        $response->assertOk()
            ->assertJsonStructure([
                'message',
                'data',
            ]);
    }

    public function test_show_with_invalid_token_401()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer invalid_token',
        ])->getJson($this->api);

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthenticated.',
            ]);
    }

    public function test_show_validation_422()
    {
        $user = $this->superAdmin();
        $token = $this->getAccessToken($user);

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$token}",
        ])->getJson($this->api);

        $response->assertStatus(422)
            ->assertJsonStructure(['role']);
    }

    public function test_show_role_id_not_found_404()
    {
        $user = $this->superAdmin();
        $token = $this->getAccessToken($user);

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$token}",
        ])->getJson(str_replace(':id', Str::uuid(), $this->api));

        $response->assertStatus(404)
            ->assertJsonStructure(['message']);
    }
}
