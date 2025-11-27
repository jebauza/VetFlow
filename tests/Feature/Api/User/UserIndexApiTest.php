<?php

namespace Tests\Feature\Api\User;

use App\Modules\User\Models\User;
use Tests\TestCase;
use App\Modules\User\Repositories\UserRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserIndexApiTest extends TestCase
{
    use RefreshDatabase;

    private $api = 'api/users';

    protected UserRepository $userRepo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userRepo = new UserRepository(new User);
    }

    public function test_index_200()
    {
        $user = $this->superAdmin();
        $token = $this->getAccessToken($user);

        $this->withHeaders(['Authorization' => "Bearer {$token}",])
            ->getJson($this->api)
            ->assertOk()
            ->assertJsonStructure([
                'message',
                'data',
            ]);
    }

    public function test_index_with_seach_200()
    {
        $user = $this->superAdmin();
        $token = $this->getAccessToken($user);

        $names = $this->userRepo->all()->pluck(User::NAME)->toArray();
        $dataString = $this->getMostRepeatedSubstring($names, 2);
        $query = http_build_query([
            'search' => $dataString['substring'] ?? ''
        ]);

        $this->withHeaders(['Authorization' => "Bearer {$token}",])
            ->getJson("{$this->api}?{$query}")
            ->assertOk()
            ->assertJsonStructure([
                'message',
                'data',
            ])
            ->assertJsonCount($dataString['repetitions'], 'data');
    }

    public function test_index_with_invalid_token_401()
    {
        $this->withHeaders(['Authorization' => 'Bearer invalid_token',])
            ->getJson($this->api)
            ->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthenticated.',
            ]);
    }
}
