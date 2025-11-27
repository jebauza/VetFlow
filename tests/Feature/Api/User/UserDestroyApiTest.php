<?php

namespace Tests\Feature\Api\User;

use Tests\TestCase;
use Illuminate\Support\Str;
use App\Modules\User\Models\User;
use App\Modules\User\Repositories\UserRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserDestroyApiTest extends TestCase
{
    use RefreshDatabase;

    private $api = 'api/users/:id';

    protected UserRepository $userRepo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userRepo = new UserRepository(new User);
    }

    public function test_destroy_200()
    {
        $user = $this->superAdmin();
        $token = $this->getAccessToken($user);
        $userId = $this->userRepo->firstRandom()->{User::ID};

        $this->withHeaders(['Authorization' => "Bearer {$token}",])
            ->deleteJson(str_replace(':id', $userId, $this->api))
            ->assertOk()
            ->assertJson([
                'message' => __('Deleted successfully'),
            ]);
    }

    public function test_destroy_with_invalid_token_401()
    {
        $this->withHeaders(['Authorization' => 'Bearer invalid_token',])
            ->deleteJson($this->api)
            ->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthenticated.',
            ]);
    }

    public function test_destroy_validation_422()
    {
        $user = $this->superAdmin();
        $token = $this->getAccessToken($user);

        $this->withHeaders(['Authorization' => "Bearer {$token}",])
            ->getJson($this->api)
            ->assertStatus(422)
            ->assertJsonStructure(['user']);
    }

    public function test_destroy_user_id_not_found_404()
    {
        $user = $this->superAdmin();
        $token = $this->getAccessToken($user);

        $this->withHeaders(['Authorization' => "Bearer {$token}",])
            ->getJson(str_replace(':id', Str::uuid()->toString(), $this->api))
            ->assertStatus(404)
            ->assertJsonStructure(['message']);
    }
}
