<?php

namespace Tests\Feature\Api\Role;

use Tests\Feature\Api\ApiTestCase;
use Illuminate\Support\Str;
use App\Modules\Role\Models\Role;
use App\Modules\User\Models\User;
use App\Modules\Role\Resources\RoleResource;
use App\Modules\Role\Repositories\RoleRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RoleShowApiTest extends ApiTestCase
{
    use RefreshDatabase;

    private $api = 'api/roles/:id';
    private string $token;
    protected RoleRepository $roleRepo;
    protected Role $role;

    protected function setUp(): void
    {
        parent::setUp();
        $this->roleRepo = new RoleRepository(new Role);

        $userAuth = $this->superAdmin();
        $this->token = $this->getAccessToken($userAuth);

        /** @var Role */
        $this->role = $this->roleRepo->randomWithRelations(['permissions:id,name']);
    }

    public function test_show_unauthorized_401()
    {
        $this->assertEndpointRequiresAuth(self::GET, $this->api);
    }

    public function test_show_forbidden_403()
    {
        $this->assertEndpointReturnsForbidden(
            self::GET,
            str_replace(':id', $this->role->{Role::ID}, $this->api)
        );
    }

    public function test_show_not_found_404()
    {
        $this->assertEndpointReturnsNotFound(
            self::GET,
            str_replace(':id', Str::uuid(), $this->api),
            $this->token
        );
    }

    public function test_show_ok_200()
    {
        $data = json_decode((new RoleResource($this->role))->toJson(), true);

        $this->withHeaders(['Authorization' => "Bearer {$this->token}",])
            ->getJson(str_replace(':id', $this->role->{Role::ID}, $this->api))
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
            ->assertJsonPath('data', $data);
    }

    public function test_show_validation_422()
    {
        // Data invalid UUID
        $this->withHeaders(['Authorization' => "Bearer {$this->token}"])
            ->getJson(str_replace(':id', 'invalid-uuid', $this->api))
            ->assertStatus(422)
            ->assertJsonPath('message', __('Validation errors'))
            ->assertJsonStructure([
                'message',
                'errors' => ['role'],
            ]);
    }
}
