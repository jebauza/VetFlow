<?php

namespace Tests\Feature\Api\Auth;

use Tests\TestCase;
use App\Models\User;
use App\Modules\Permission\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MeApiTest extends TestCase
{
    use RefreshDatabase;

    private $api = 'api/auth/me';

    public function test_me()
    {
        $user = $this->superAdmin();
        $token = $this->getAccessToken($user);

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$token}",
        ])->getJson($this->api);

        $response->assertOk()
            ->assertJson([
                'id' => $user->{User::ID},
                'email' => $user->{User::EMAIL},
                'name' => $user->{User::NAME},
                'surname' => $user->{User::SURNAME},
                'avatar' => $user->{User::AVATAR},
                'permissions' => $user->getAllPermissions()->pluck(Permission::NAME)->toArray(),
                'roles' => $user->getRoleNames()->toArray(),
            ]);
    }

    public function test_me_with_invalid_token()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer invalid_token',
        ])->getJson($this->api);

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthenticated.',
            ]);
    }
}
