<?php

namespace Tests\Feature\Api\User;

use Tests\Feature\Api\ApiTestCase;
use App\Modules\User\Models\User;
use App\Modules\User\Resources\UserResource;
use App\Modules\User\Repositories\UserRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserIndexApiTest extends ApiTestCase
{
    use RefreshDatabase;

    private $api = 'api/users';
    private string $token;
    protected UserRepository $userRepo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userRepo = new UserRepository(new User);

        $users = User::factory(5)->create();
        $this->token = $this->getAccessToken($users->random());
    }

    public function test_index_invalid_token_401()
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
                        'surname',
                        'email',
                        'avatar',
                        'phone',
                        'type_document',
                        'n_document',
                        'birth_date',
                        'designation',
                        'gender',
                        'roles' => [
                            '*' => [
                                'id',
                                'name',
                            ]
                        ],
                        'all_permissions' => [
                            '*' => [
                                'id',
                                'name',
                            ]
                        ]
                    ]
                ]
            ])
            ->assertJsonPath('message', __('OK'));

        $users = $this->userRepo->search(null, true);
        $data = json_decode((UserResource::collection($users))->toJson(), true);

        $response->assertJsonCount($users->count(), 'data')
            ->assertJsonPath('data', $data);
    }

    public function test_index_search_200()
    {
        $search = 'a';
        $query = http_build_query([
            'search' => $search,
        ]);
        $total = $this->userRepo->search($search)->count();

        $this->withHeaders(['Authorization' => "Bearer {$this->token}"])
            ->getJson("{$this->api}?{$query}")
            ->assertOk()
            ->assertJsonPath('message', __('OK'))
            ->assertJsonCount($total, 'data');
    }
}
