<?php

namespace Tests\Feature\Api\Auth;

use Tests\Feature\Api\ApiTestCase;
use App\Modules\User\Models\User;
use App\Modules\User\Resources\UserResource;
use App\Modules\User\Repositories\UserRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthMeApiTest extends ApiTestCase
{
    use RefreshDatabase;

    private $api = 'api/auth/me';
    private string $token;
    protected UserRepository $userRepo;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userRepo = new UserRepository(new User);
        $userAuth = User::factory()->create();
        $this->token = $this->getAccessToken($userAuth);
    }

    public function test_me_unauthorized_401()
    {
        $this->assertEndpointRequiresAuth(self::GET, $this->api);
    }

    public function test_me_200()
    {
        $response = $this->withHeaders(['Authorization' => "Bearer {$this->token}",])
            ->getJson($this->api)
            ->assertStatus(200)
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
                ]
            ])
            ->assertJsonPath('message', __('OK'));

        $user = $this->userRepo->findOrFail($response->json('data.id'), true);
        $meData = json_decode((new UserResource($user))->toJson(), true);

        $response->assertJsonPath('data', $meData);
    }
}
