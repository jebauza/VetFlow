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

    public function test_role_show()
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

    public function test_roles_with_invalid_token()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer invalid_token',
        ])->getJson($this->api);

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthenticated.',
            ]);
    }

    public function test_role_validation_uuid()
    {
        $user = $this->superAdmin();
        $token = $this->getAccessToken($user);

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$token}",
        ])->getJson($this->api);

        $response->assertStatus(422)
            ->assertJsonStructure(['id']);
    }

    public function test_role_with_invalid_id()
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
