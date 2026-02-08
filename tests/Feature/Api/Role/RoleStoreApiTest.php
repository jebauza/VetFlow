<?php

namespace Tests\Feature\Api\Role;

use Tests\Feature\Api\ApiTestCase;
use Illuminate\Support\Str;
use App\Modules\Role\Models\Role;
use App\Modules\User\Models\User;
use App\Modules\Role\Resources\RoleResource;
use App\Modules\Permission\Models\Permission;
use App\Modules\Permission\Repositories\PermissionRepository;
use App\Modules\Role\Repositories\RoleRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RoleStoreApiTest extends ApiTestCase
{
    use RefreshDatabase;

    private $api = 'api/roles';
    private string $token;
    private array $payload = [];
    protected PermissionRepository $permissionRepo;
    protected RoleRepository $roleRepo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->permissionRepo = new PermissionRepository(new Permission);
        $this->roleRepo = new RoleRepository(new Role);

        $userAuth = User::factory()->create();
        $this->token = $this->getAccessToken($userAuth);
        $this->payload = [
            "name" => "Role Test",
            "permission_ids" => $this->permissionRepo->random(5)
                ->pluck(Permission::ID)
                ->toArray(),
        ];
    }

    public function test_store_unauthorized_401()
    {
        $this->assertEndpointRequiresAuth(self::POST, $this->api);
    }

    public function test_store_201()
    {
        $response = $this->withHeaders(['Authorization' => "Bearer {$this->token}",])
            ->postJson($this->api, $this->payload)
            ->assertCreated()
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id',
                    'name',
                    'date',
                    'permissions' => [
                        '*' => [
                            'id',
                            'name',
                        ]
                    ]
                ]
            ])
            ->assertJsonPath('message', __('Created'))
            ->assertJsonPath('data.name', $this->payload['name'])
            ->assertJsonCount(count($this->payload['permission_ids']), 'data.permissions');

        $this->assertDatabaseHas(Role::TABLE, [
            Role::ID => $response->json('data.id'),
            Role::NAME => $this->payload['name'],
        ]);

        $role = $this->roleRepo->findWithRelations(
            $response->json('data.id'),
            ['permissions']
        );

        $this->assertEqualsCanonicalizing(
            $this->payload['permission_ids'],
            $role->permissions->pluck(Permission::ID)->toArray()
        );

        $data = json_decode((new RoleResource($role))->toJson(), true);
        $response->assertJsonPath('data', $data);
    }

    public function test_store_validation_422()
    {
        // Data required
        $this->withHeaders(['Authorization' => "Bearer {$this->token}",])
            ->postJson($this->api)
            ->assertStatus(422)
            ->assertJsonPath('message', __('Validation errors'))
            ->assertJsonStructure([
                'message',
                'errors' => ['name', 'permission_ids'],
            ]);

        // Data max
        $data = $this->payload;
        $data['name'] = Str::random(256);
        $this->postJson($this->api, $data)
            ->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => ['name'],
            ]);

        // Data not array
        $data = $this->payload;
        $data['permission_ids'] = 'not an array';
        $this->postJson($this->api, $data)
            ->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => ['permission_ids'],
            ]);

        // Data permission_ids.* not uuid
        $data = $this->payload;
        $data['permission_ids'] = ['not-a-uuid'];
        $this->postJson($this->api, $data)
            ->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => ['permission_ids.0'],
            ]);

        // Name already exists DB
        $data = $this->payload;
        $data['name'] = $this->roleRepo->random()->{Role::NAME};
        $this->postJson($this->api, $data)
            ->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => ['name'],
            ]);

        // Permission_ids not in DB
        $data = $this->payload;
        $data['permission_ids'] = [Str::uuid()->toString()];
        $this->postJson($this->api, $data)
            ->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => ['permission_ids.0'],
            ]);
    }
}
