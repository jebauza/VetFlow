<?php

namespace Tests\Feature\Api\User;

use App\Modules\User\Models\User;
use Tests\Feature\Api\ApiTestCase;
use App\Modules\User\Resources\UserResource;
use App\Modules\User\Repositories\UserRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserOffsetPaginateApiTest extends ApiTestCase
{
    use RefreshDatabase;

    private $api = 'api/users/offset-paginate';
    private string $token;
    private array $payload = [];
    protected UserRepository $userRepo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userRepo = new UserRepository(new User);

        $users = User::factory(5)->create();
        $this->payload = [
            'offset' => 2,
            'limit' => 2,
        ];
        $this->token = $this->getAccessToken($users->random());
    }

    public function test_offset_paginate_invalid_token_401()
    {
        $this->assertEndpointRequiresAuth(self::GET, $this->api);
    }

    public function test_offset_paginate_200()
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
                ],
                'meta' => [
                    'offset',
                    'limit',
                    'total',
                ],
            ])
            ->assertJsonPath('message', __('OK'))
            ->assertJsonPath('meta.offset', 0);

        $users = $this->userRepo->search(null, true);
        $total = $users->count();
        $data = json_decode((UserResource::collection($users))->toJson(), true);
        $response->assertJsonPath('meta.total', $total)
            ->assertJsonPath('data', $data);

        // Data with payload
        $query = http_build_query($this->payload);
        $this->getJson("{$this->api}?{$query}")
            ->assertOk()
            ->assertJsonCount($this->payload['limit'], 'data')
            ->assertJsonPath('meta', [
                'offset' => $this->payload['offset'],
                'limit' => $this->payload['limit'],
                'total' => $total,
            ]);
    }

    public function test_offset_paginate_search_200()
    {
        $search = 'a';
        $query = http_build_query([
            'search' => $search,
        ]);
        $total = $this->userRepo->searchCount($search);

        $this->withHeaders(['Authorization' => "Bearer {$this->token}",])
            ->getJson("{$this->api}?{$query}")
            ->assertOk()
            ->assertJsonPath('meta.total', $total);
    }

    public function test_offset_paginate_validation_422(): void
    {
        // Data integer
        $query = http_build_query([
            'offset'  => 'not integer',
            'limit' => 'not integer',
        ]);
        $this->withHeaders(['Authorization' => "Bearer {$this->token}",])
            ->getJson("{$this->api}?{$query}")
            ->assertStatus(422)
            ->assertJsonPath('message', __('Validation errors'))
            ->assertJsonStructure([
                'message',
                'errors' => ['offset', 'limit'],
            ]);

        // Data min
        $query = http_build_query([
            'offset'  => -1,
            'limit' => 0,
        ]);
        $this->getJson("{$this->api}?{$query}")
            ->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => ['offset', 'limit'],
            ]);

        // Data max
        $query = http_build_query([
            'limit' => 101,
        ]);
        $this->getJson("{$this->api}?{$query}")
            ->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => ['limit'],
            ]);
    }
}
