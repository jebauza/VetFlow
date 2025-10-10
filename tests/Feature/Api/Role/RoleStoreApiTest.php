<?php

namespace Tests\Feature\Api\Auth;

use App\Models\Permission;
use Tests\TestCase;
use Illuminate\Support\Str;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RoleStoreApiTest extends TestCase
{
    use RefreshDatabase;

    private $api = 'api/roles';

    public function test_store()
    {
        $user = $this->superAdmin();
        $token = $this->getAccessToken($user);
        $permissions = Permission::inRandomOrder()->limit(5)->get();

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$token}",
        ])->postJson($this->api, [
            "name" => "Role Test",
            "permission_ids" => $permissions->pluck(Permission::ID)->toArray(),
        ]);

        $response->assertCreated()
            ->assertJson([
                'message' => 'Created successfully',
                'data' => [
                    "id" => $response->json('data.id'),
                    "name" => "Role Test",
                    "permissions" => $permissions->map(function ($permission) {
                        return [
                            'id' => $permission->{Permission::ID},
                            'name' => $permission->{Permission::NAME},
                        ];
                    })->sortBy(Permission::ID)->values()->toArray(),
                ]
            ]);
    }

    public function test_store_with_invalid_token()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer invalid_token',
        ])->postJson($this->api);

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthenticated.',
            ]);
    }

    public function test_store_validation()
    {
        $user = $this->superAdmin();
        $token = $this->getAccessToken($user);

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$token}",
        ])->postJson($this->api, [
            "name" => "",
            "permission_ids" => [
                'not-a-uuid',
            ],
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure(['name', 'permission_ids.0']);
    }

    public function test_store_validation_2()
    {
        $user = $this->superAdmin();
        $token = $this->getAccessToken($user);

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$token}",
        ])->postJson($this->api, [
            "name" => "Role Test",
            "permission_ids" => [
                Str::uuid(),
            ],
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure(['permission_ids.0']);
    }
}
