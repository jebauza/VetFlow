<?php

namespace Tests\Feature\Api\Role;

use Tests\TestCase;
use Illuminate\Support\Str;
use App\Modules\Role\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RoleDestroyApiTest extends TestCase
{
    use RefreshDatabase;

    private $api = 'api/roles/:id';

    public function test_destroy_200()
    {
        $user = $this->superAdmin();
        $token = $this->getAccessToken($user);
        $role = Role::inRandomOrder()->first();

        $this->withHeaders(['Authorization' => "Bearer {$token}",])
            ->deleteJson(str_replace(':id', $role->{Role::ID}, $this->api))
            ->assertOk()
            ->assertJsonStructure([
                'message',
                'data',
            ]);
    }

    public function test_destroy_with_invalid_token_401()
    {
        $this->withHeaders(['Authorization' => 'Bearer invalid_token',])
            ->deleteJson($this->api)
            ->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthenticated.',
            ]);
    }

    public function test_destroy_validation_422()
    {
        $user = $this->superAdmin();
        $token = $this->getAccessToken($user);

        $this->withHeaders(['Authorization' => "Bearer {$token}",])
            ->getJson($this->api)
            ->assertStatus(422)
            ->assertJsonStructure(['role']);
    }

    public function test_destroy_role_id_not_found_404()
    {
        $user = $this->superAdmin();
        $token = $this->getAccessToken($user);

        $this->withHeaders(['Authorization' => "Bearer {$token}",])
            ->getJson(str_replace(':id', Str::uuid(), $this->api))
            ->assertStatus(404)
            ->assertJsonStructure(['message']);
    }
}
