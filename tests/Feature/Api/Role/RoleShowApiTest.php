<?php

namespace Tests\Feature\Api\Role;

use Tests\TestCase;
use Illuminate\Support\Str;
use App\Modules\Role\Models\Role;
use App\Modules\Role\Resources\RoleResource;
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
        $showData = json_decode((new RoleResource($role))->toJson(), true);

        $this->withHeaders(['Authorization' => "Bearer {$token}",])
            ->getJson(str_replace(':id', $role->{Role::ID}, $this->api))
            ->assertOk()
            ->assertJson([
                'message' => 'Request processed successfully',
                'data' => $showData
            ]);
    }

    public function test_show_with_invalid_token_401()
    {
        $this->withHeaders(['Authorization' => 'Bearer invalid_token',])
            ->getJson($this->api)
            ->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthenticated.',
            ]);
    }

    public function test_show_validation_422()
    {
        $user = $this->superAdmin();
        $token = $this->getAccessToken($user);

        $this->withHeaders(['Authorization' => "Bearer {$token}",])
            ->getJson($this->api)
            ->assertStatus(422)
            ->assertJsonStructure(['role']);
    }

    public function test_show_id_not_found_404()
    {
        $user = $this->superAdmin();
        $token = $this->getAccessToken($user);

        $this->withHeaders(['Authorization' => "Bearer {$token}",])
            ->getJson(str_replace(':id', Str::uuid()->toString(), $this->api))
            ->assertStatus(404)
            ->assertJsonStructure(['message']);
    }
}
