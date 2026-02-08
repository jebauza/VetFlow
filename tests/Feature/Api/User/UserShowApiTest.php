<?php

namespace Tests\Feature\Api\User;

use Illuminate\Support\Str;
use App\Modules\User\Models\User;
use Tests\Feature\Api\ApiTestCase;
use App\Modules\User\Resources\UserResource;
use App\Modules\User\Repositories\UserRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserShowApiTest extends ApiTestCase
{
    use RefreshDatabase;

    private $api = 'api/users/:id';
    private string $token;
    protected UserRepository $userRepo;
    protected User $userAuth;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userRepo = new UserRepository(new User);

        $this->userAuth = User::factory()->create();
        $this->token = $this->getAccessToken($this->userAuth);
    }

    public function test_show_unauthorized_401()
    {
        $this->assertEndpointRequiresAuth(self::GET, $this->api);
    }

    public function test_show_200()
    {
        $response = $this->withHeaders(['Authorization' => "Bearer {$this->token}",])
            ->getJson(str_replace(':id', $this->userAuth->{User::ID}, $this->api))
            ->assertOk()
            ->assertJsonPath('message', __('OK'))
            ->assertJsonStructure([
                'message',
                'data' => [
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
                ],
            ])
            ->assertJsonPath('message', __('OK'));

        $user = $this->userRepo->findWithRelations(
            $response->json('data.id'),
            ['permissions:id,name', 'roles:id,name']
        );
        $data = json_decode((new UserResource($user))->toJson(), true);

        $response->assertJsonPath('data', $data);
    }

    public function test_show_404()
    {
        $this->assertEndpointReturnsNotFound(
            self::GET,
            str_replace(':id', Str::uuid(), $this->api),
            [],
            $this->token
        );
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
                'errors' => ['user'],
            ]);
    }
}
