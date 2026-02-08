<?php

namespace Tests\Feature\Api\Role;

use Tests\Feature\Api\ApiTestCase;
use App\Modules\Role\Models\Role;
use App\Modules\User\Models\User;
use App\Modules\Role\Resources\RoleResource;
use App\Modules\Role\Repositories\RoleRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RoleIndexApiTest extends ApiTestCase
{
    use RefreshDatabase;

    private $api = 'api/roles';
    private string $token;
    protected RoleRepository $roleRepo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->roleRepo = new RoleRepository(new Role);

        $userAuth = User::factory()->create();
        $this->token = $this->getAccessToken($userAuth);
    }

    public function test_index_unauthorized_401()
    {
        $this->assertEndpointRequiresAuth(self::GET, $this->api);
    }

    public function test_index_200()
    {
        $response = $this->withHeaders(['Authorization' => "Bearer {$this->token}",])
            ->getJson($this->api)
            ->assertOk()
            ->assertJsonStructure([
                'message',
                'data' => [
                    '*' => [
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
                ]
            ])
            ->assertJsonPath('message', __('OK'));

        $roles = $this->roleRepo->search(null, true);
        $data = json_decode((RoleResource::collection($roles))->toJson(), true);

        $response->assertJsonCount($roles->count(), 'data')
            ->assertJsonPath('data', $data);
    }

    public function test_index_search_200()
    {
        $names = $this->roleRepo->queryAll()->pluck(Role::NAME)->toArray();
        $dataString = $this->getMostRepeatedSubstring($names, 2);
        $search = $dataString['substring'] ?? '';
        $query = http_build_query([
            'search' => $search,
        ]);
        $total = $this->roleRepo->searchCount($search, true);

        $this->withHeaders(['Authorization' => "Bearer {$this->token}"])
            ->getJson("{$this->api}?{$query}")
            ->assertOk()
            ->assertJsonPath('message', __('OK'))
            ->assertJsonCount($total, 'data');

        $this->assertEquals(
            $total,
            $dataString['repetitions']
        );
    }
}
