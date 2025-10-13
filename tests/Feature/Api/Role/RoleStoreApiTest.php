<?php

namespace Tests\Feature\Api\Role;

use Tests\TestCase;
use Illuminate\Support\Str;
use App\Modules\Role\Models\Role;
use App\Modules\Permission\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RoleStoreApiTest extends TestCase
{
    use RefreshDatabase;

    private $api = 'api/roles';

    public function test_store_201()
    {
        $user = $this->superAdmin();
        $token = $this->getAccessToken($user);
        $permissions = Permission::inRandomOrder()->limit(5)->get();

        $response = $this->withHeaders(['Authorization' => "Bearer {$token}",])
            ->postJson($this->api, [
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

    public function test_store_with_invalid_token_401()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer invalid_token',
        ])->postJson($this->api);

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthenticated.',
            ]);
    }

    public function test_store_validation_422()
    {
        $user = $this->superAdmin();
        $token = $this->getAccessToken($user);


        // Data not valid
        $this->withHeaders(['Authorization' => "Bearer {$token}",])
            ->postJson($this->api, [
                "name" => "",
                "permission_ids" => [
                    'not-a-uuid',
                ],
            ])
            ->assertStatus(422)
            ->assertJsonStructure(['name', 'permission_ids.0']);


        // Name already exists DB
        $this->withHeaders(['Authorization' => "Bearer {$token}",])
            ->postJson($this->api, [
                "name" => Role::first()->{Role::NAME},
                "permission_ids" => [],
            ])
            ->assertStatus(422)
            ->assertJsonStructure(['name']);


        // Permission_ids not send
        $this->withHeaders(['Authorization' => "Bearer {$token}",])
            ->postJson($this->api, [
                "name" => 'Role Test',
            ])
            ->assertStatus(422)
            ->assertJsonStructure(['permission_ids']);


        // Permission_ids not in DB
        $this->withHeaders(['Authorization' => "Bearer {$token}",])
            ->postJson($this->api, [
                "name" => 'Role Test',
                "permission_ids" => [
                    Str::uuid(),
                ],
            ])
            ->assertStatus(422)
            ->assertJsonStructure(['permission_ids.0']);
    }
}
