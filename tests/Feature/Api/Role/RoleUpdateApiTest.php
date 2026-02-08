<?php

namespace Tests\Feature\Api\Role;

use Tests\Feature\Api\ApiTestCase;
use Illuminate\Support\Str;
use App\Modules\Role\Models\Role;
use App\Modules\User\Models\User;
use App\Modules\Role\Resources\RoleResource;
use App\Modules\Permission\Models\Permission;
use App\Modules\Role\Repositories\RoleRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Modules\Permission\Repositories\PermissionRepository;

class RoleUpdateApiTest extends ApiTestCase
{
    use RefreshDatabase;

    private $api = 'api/roles/:id';
    private string $token;
    private array $payload = [];
    protected RoleRepository $roleRepo;
    protected PermissionRepository $permissionRepo;
    private Role $role;

    protected function setUp(): void
    {
        parent::setUp();
        $this->permissionRepo = new PermissionRepository(new Permission);
        $this->roleRepo = new RoleRepository(new Role);

        $userAuth = User::factory()->create();
        $this->token = $this->getAccessToken($userAuth);

        /** @var Role $this->role */
        $this->role = $this->roleRepo->random();
        $this->payload = [
            "name" => "Role Test",
            "permission_ids" => $this->permissionRepo->random(5)
                ->pluck(Permission::ID)
                ->toArray(),
        ];
    }

    public function test_update_unauthorized_401()
    {
        $this->assertEndpointRequiresAuth(self::GET, $this->api);
    }

    public function test_update_200()
    {
        $response = $this->withHeaders(['Authorization' => "Bearer {$this->token}",])
            ->putJson(
                str_replace(':id', $this->role->{Role::ID}, $this->api),
                $this->payload
            )
            ->assertOk()
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
            ->assertJsonPath('message', __('OK'))
            ->assertJsonPath('data.id', $this->role->{Role::ID})
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

        $data = json_decode(
            (new RoleResource($role))->toJson(),
            true
        );
        $response->assertJsonPath('data', $data);
    }

    public function test_update_404()
    {
        $this->assertEndpointReturnsNotFound(
            self::PUT,
            str_replace(':id', Str::uuid(), $this->api),
            $this->payload,
            $this->token
        );
    }

    public function test_update_validation_422()
    {
        // Data user_id invalid UUID and required fields
        $this->withHeaders(['Authorization' => "Bearer {$this->token}"])
            ->putJson(str_replace(':id', 'invalid-uuid', $this->api))
            ->assertStatus(422)
            ->assertJsonPath('message', __('Validation errors'))
            ->assertJsonStructure([
                'message',
                'errors' => ['name', 'permission_ids', 'role'],
            ]);

        $api = str_replace(':id', $this->role->{Role::ID}, $this->api);

        // Data max
        $data = $this->payload;
        $data['name'] = Str::random(256);
        $this->putJson($api, $data)
            ->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => ['name'],
            ]);

        // Data not array
        $data = $this->payload;
        $data['permission_ids'] = 'not an array';
        $this->putJson($api, $data)
            ->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => ['permission_ids'],
            ]);

        // Data permission_ids.* not uuid
        $data = $this->payload;
        $data['permission_ids'] = ['not-a-uuid'];
        $this->putJson($api, $data)
            ->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => ['permission_ids.0'],
            ]);

        // Name already exists DB
        $data = $this->payload;
        $data['name'] = $this->roleRepo->query()
            ->where(Role::NAME, '<>', $this->role->{Role::NAME})
            ->first()
            ->{Role::NAME};
        $this->putJson($api, $data)
            ->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => ['name'],
            ]);

        // Permission_ids not in DB
        $data = $this->payload;
        $data['permission_ids'] = [Str::uuid()->toString()];
        $this->putJson($api, $data)
            ->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => ['permission_ids.0'],
            ]);
    }
}
