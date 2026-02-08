<?php

namespace Tests\Feature\Api\Permission;

use App\Modules\User\Models\User;
use Tests\Feature\Api\ApiTestCase;
use App\Modules\Permission\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Modules\Permission\Resources\PermissionResource;
use App\Modules\Permission\Repositories\PermissionRepository;

class PermissionIndexApiTest extends ApiTestCase
{
    use RefreshDatabase;

    private $api = 'api/permissions';
    private string $token;
    protected PermissionRepository $permissionRepo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->permissionRepo = new PermissionRepository(new Permission());

        $user = User::factory()->create();
        $this->token = $this->getAccessToken($user);
    }

    public function test_index_unauthorized_401()
    {
        $this->assertEndpointRequiresAuth(self::GET, $this->api);
    }

    public function test_index_200()
    {
        $response = $this->withHeaders(['Authorization' => "Bearer {$this->token}"])
            ->getJson($this->api)
            ->assertOk()
            ->assertJsonStructure([
                'message',
                'data' => [
                    '*' => [
                        'id',
                        'name',
                    ]
                ]
            ])
            ->assertJsonPath('message', __('OK'));

        $permissions = $this->permissionRepo->all();
        $data = json_decode((PermissionResource::collection($permissions))->toJson(), true);
        $response->assertJsonCount($permissions->count(), 'data')
            ->assertJsonPath('data', $data);
    }
}
