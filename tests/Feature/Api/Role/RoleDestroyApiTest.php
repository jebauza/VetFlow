<?php

namespace Tests\Feature\Api\Role;

use Tests\Feature\Api\ApiTestCase;
use Illuminate\Support\Str;
use App\Modules\Role\Models\Role;
use App\Modules\User\Models\User;
use App\Modules\Role\Repositories\RoleRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RoleDestroyApiTest extends ApiTestCase
{
    use RefreshDatabase;

    private $api = 'api/roles/:id';
    private string $token;
    protected RoleRepository $roleRepo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->roleRepo = new RoleRepository(new Role);

        $userAuth = User::factory()->create();
        $this->token = $this->getAccessToken($userAuth);
    }

    public function test_destroy_unauthorized_401()
    {
        $this->assertEndpointRequiresAuth(self::DELETE, $this->api);
    }

    public function test_destroy_200()
    {
        $role = $this->roleRepo->random();

        $this->withHeaders(['Authorization' => "Bearer {$this->token}",])
            ->deleteJson(str_replace(':id', $role->{Role::ID}, $this->api))
            ->assertOk()
            ->assertJson([
                'message' => __('Deleted successfully'),
            ]);

        $this->assertDatabaseMissing(Role::TABLE, [
            Role::ID => $role->{Role::ID},
        ]);
    }

    public function test_destroy_404()
    {
        $this->assertEndpointReturnsNotFound(
            self::DELETE,
            str_replace(':id', Str::uuid(), $this->api),
            [],
            $this->token
        );
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
