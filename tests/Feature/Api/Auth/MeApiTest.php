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

    public function test_me_200()
    {
        $user = $this->superAdmin();
        $token = $this->getAccessToken($user);

        $this->withHeaders(['Authorization' => "Bearer {$token}",])
            ->getJson($this->api)
            ->assertOk()
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

    public function test_me_with_invalid_token_401()
    {
        $this->withHeaders(['Authorization' => 'Bearer invalid_token',])
            ->getJson($this->api)
            ->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthenticated.',
            ]);
    }
}
