<?php

namespace Tests\Feature\Api\Auth;

use Tests\Feature\Api\ApiTestCase;
use App\Modules\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthRefreshApiTest extends ApiTestCase
{
    use RefreshDatabase;

    private $api = 'api/auth/refresh';
    private User $userAuth;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userAuth = User::factory()->create();
        $this->token = $this->getAccessToken($this->userAuth);
    }

    public function test_refresh_unauthorized_401()
    {
        $this->assertEndpointRequiresAuth(self::GET, $this->api);
    }

    public function test_refresh_200()
    {
        $this->withHeaders(['Authorization' => "Bearer {$this->token}",])
            ->getJson($this->api)
            ->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'access_token',
                    'token_type',
                    'expires_in',
                    'expires_at',
                    'user' => [
                        'id',
                        'name',
                        'email',
                    ]
                ]
            ])
            ->assertJsonPath('message', __('OK'))
            ->assertJsonPath('data.user', [
                'id' => $this->userAuth->{User::ID},
                'name' => $this->userAuth->{User::NAME},
                'email' => $this->userAuth->{User::EMAIL},
            ]);
    }
}
