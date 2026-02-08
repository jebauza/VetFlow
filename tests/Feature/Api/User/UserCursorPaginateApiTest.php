<?php

namespace Tests\Feature\Api\User;

use App\Modules\User\Models\User;
use Tests\Feature\Api\ApiTestCase;
use App\Modules\User\Repositories\UserRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserCursorPaginateApiTest extends ApiTestCase
{
    use RefreshDatabase;

    private $api = 'api/users/cursor-paginate';
    private string $token;
    private array $payload = [];
    protected UserRepository $userRepo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userRepo = new UserRepository(new User);

        $users = User::factory(5)->create();
        $this->payload = [
            'per_page' => 2,
        ];
        $this->token = $this->getAccessToken($users->random());
    }

    public function test_cursor_paginate_invalid_token_401()
    {
        $this->assertEndpointRequiresAuth(self::GET, $this->api);
    }

    public function test_cursor_paginate_200()
    {
        $this->withHeaders(['Authorization' => "Bearer {$this->token}"])
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
                    'per_page',
                    'next_cursor',
                    'prev_cursor',
                ],
            ])
            ->assertJsonPath('message', __('OK'))
            ->assertJsonPath('meta.prev_cursor', null);

        // Data with payload
        $query = http_build_query($this->payload);
        $response1 = $this->getJson("{$this->api}?{$query}")
            ->assertOk()
            ->assertJsonCount($this->payload['per_page'], 'data')
            ->assertJsonPath('meta.per_page', $this->payload['per_page'])
            ->assertJsonPath('meta.prev_cursor', null);

        $query = http_build_query(array_merge($this->payload, ['cursor' => $response1->json('meta.next_cursor')]));
        $response2 = $this->getJson("{$this->api}?{$query}")
            ->assertOk()
            ->assertJsonCount($this->payload['per_page'], 'data')
            ->assertJsonPath('meta.per_page', $this->payload['per_page']);

        $this->assertNotEquals($response1->json('data'), $response2->json('data'), 'The list of users should have changed.');
    }

    public function test_cursor_paginate_search_200()
    {
        $search = 'a';
        $total = $this->userRepo->searchCount($search);

        $query = http_build_query(array_merge($this->payload, ['search' => $search]));
        $response = $this->withHeaders(['Authorization' => "Bearer {$this->token}",])
            ->getJson("{$this->api}?{$query}")
            ->assertOk()
            ->assertJsonPath('meta.per_page', $this->payload['per_page']);

        $this->assertLessThanOrEqual($this->payload['per_page'], count($response->json('data')));
        $this->assertTrue(
            ($response->json('meta.next_cursor') && $total > $this->payload['per_page']) ||
                (is_null($response->json('meta.next_cursor')) && $total <= $this->payload['per_page'])
        );
    }

    public function test_cursor_paginate_validation_422(): void
    {
        // Data integer
        $query = http_build_query([
            'per_page'  => 'not integer',
        ]);
        $this->withHeaders(['Authorization' => "Bearer {$this->token}",])
            ->getJson("{$this->api}?{$query}")
            ->assertStatus(422)
            ->assertJsonPath('message', __('Validation errors'))
            ->assertJsonStructure([
                'message',
                'errors' => ['per_page'],
            ]);

        // Data min
        $query = http_build_query([
            'per_page' => 0,
        ]);
        $this->getJson("{$this->api}?{$query}")
            ->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => ['per_page'],
            ]);

        // Data max
        $query = http_build_query([
            'per_page' => 101,
        ]);
        $this->getJson("{$this->api}?{$query}")
            ->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => ['per_page'],
            ]);
    }
}
