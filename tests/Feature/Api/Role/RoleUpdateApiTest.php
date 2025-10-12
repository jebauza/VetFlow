<?php

namespace Tests\Feature\Api\Role;

use Tests\TestCase;
use Illuminate\Support\Str;
use App\Modules\Role\Models\Role;
use App\Modules\Permission\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RoleUpdateApiTest extends TestCase
{
    use RefreshDatabase;

    private $api = 'api/roles/:id';

    public function test_update_200()
    {
        $user = $this->superAdmin();
        $token = $this->getAccessToken($user);

        $role = Role::inRandomOrder()->first();
        $permissions = Permission::inRandomOrder()->limit(3)->get();

        $updateData = [
            'id' => $role->id,
            'name' => 'Role Test',
            'permissions' => $permissions->map(function ($permission) {
                return [
                    'id' => $permission->{Permission::ID},
                    'name' => $permission->{Permission::NAME},
                ];
            })->sortBy(Permission::ID)->values()->toArray(),
        ];

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$token}",
        ])->putJson(str_replace(':id', $role->{Role::ID}, $this->api), [
            "name" => $updateData['name'],
            "permission_ids" => $permissions->pluck(Permission::ID)->toArray(),
        ]);

        $response->assertCreated()
            ->assertJson([
                'message' => 'Updated successfully',
                'data' => $updateData,
            ]);
    }

    public function test_update_with_invalid_token_401()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer invalid_token',
        ])->putJson($this->api);

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthenticated.',
            ]);
    }

    public function test_update_validation_422()
    {
        $user = $this->superAdmin();
        $token = $this->getAccessToken($user);

        // Role_id is not uuid
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$token}",
        ])->putJson($this->api);

        $response->assertStatus(422)
            ->assertJsonStructure(['role']);


        // Data not valid
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$token}",
        ])->putJson(str_replace(':id', Str::uuid(), $this->api), [
            "name" => "",
            "permission_ids" => [
                'not-a-uuid',
            ],
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure(['name', 'permission_ids.0']);


        // Name already exists DB
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$token}",
        ])->putJson(str_replace(':id', Str::uuid(), $this->api), [
            "name" => Role::first()->{Role::NAME},
            "permission_ids" => [],
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure(['name']);


        // Permission_ids not send
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$token}",
        ])->putJson(str_replace(':id', Str::uuid(), $this->api), [
            "name" => 'Role Test',
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure(['permission_ids']);


        // Permission_ids not in DB
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$token}",
        ])->putJson(str_replace(':id', Str::uuid(), $this->api), [
            "name" => 'Role Test',
            "permission_ids" => [
                Str::uuid(),
            ],
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure(['permission_ids.0']);
    }

    public function test_update_role_id_not_found_404()
    {
        $user = $this->superAdmin();
        $token = $this->getAccessToken($user);

        $permissions = Permission::inRandomOrder()->limit(3)->get();

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$token}",
        ])->putJson(str_replace(':id', Str::uuid(), $this->api), [
            "name" => 'Role Test',
            "permission_ids" => $permissions->pluck(Permission::ID)->toArray(),
        ]);

        $response->assertStatus(404)
            ->assertJsonStructure(['message']);
    }
}
