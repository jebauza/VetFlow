<?php

namespace Tests\Feature\Api\Auth;

use Tests\Feature\Api\ApiTestCase;
use App\Modules\User\Models\User;
use Illuminate\Support\Facades\Auth;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthLogoutApiTest extends ApiTestCase
{
    use RefreshDatabase;

    private $api = 'api/auth/logout';
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();

        $user = User::factory()->create();
        $this->token = $this->getAccessToken($user);
    }

    public function test_logout_unauthorized_401()
    {
        $this->assertEndpointRequiresAuth(self::POST, $this->api);
    }

    public function test_logout_200()
    {
        $this->withHeaders(['Authorization' => "Bearer {$this->token}"])
            ->postJson($this->api)
            ->assertStatus(200)
            ->assertJson([
                'message' => 'Successfully logged out',
            ]);

        JWTAuth::unsetToken();
        Auth::forgetGuards();

        $this->withHeaders(['Authorization' => "Bearer {$this->token}"])
            ->postJson($this->api)
            ->assertStatus(401);
    }
}
