<?php

namespace Tests\Feature\Api\Role;

use Tests\Feature\Api\ApiTestCase;
use Illuminate\Support\Str;
use App\Modules\Role\Models\Role;
use App\Modules\Role\Repositories\RoleRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RoleDestroyApiTest extends ApiTestCase
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
        $this->role = $this->roleRepo->random();
    }

    public function test_destroy_unauthorized_401()
    {
        $this->assertEndpointRequiresAuth(self::DELETE, $this->api);
    }

    public function test_destroy_forbidden_403()
    {
        $this->assertEndpointReturnsForbidden(
            self::DELETE,
            str_replace(':id', $this->role->{Role::ID}, $this->api)
        );
    }

    public function test_destroy_not_found_404()
    {
        $this->assertEndpointReturnsNotFound(
            self::DELETE,
            str_replace(':id', Str::uuid(), $this->api),
            $this->token
        );
    }

    public function test_destroy_ok_200()
    {
        $this->withHeaders(['Authorization' => "Bearer {$this->token}",])
            ->deleteJson(str_replace(':id', $this->role->{Role::ID}, $this->api))
            ->assertOk()
            ->assertJson([
                'message' => __('Deleted successfully'),
            ]);

        $this->assertDatabaseMissing(Role::TABLE, [
            Role::ID => $this->role->{Role::ID},
        ]);
    }

    public function test_destroy_validation_422()
    {
        // Data invalid UUID
        $this->withHeaders(['Authorization' => "Bearer {$this->token}"])
            ->deleteJson(str_replace(':id', 'invalid-uuid', $this->api))
            ->assertStatus(422)
            ->assertJsonPath('message', __('Validation errors'))
            ->assertJsonStructure([
                'message',
                'errors' => ['role'],
            ]);
    }
}
