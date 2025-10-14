<?php

namespace Tests\Feature\Api\Role;

use Tests\TestCase;
use App\Modules\Role\Models\Role;
use App\Modules\Role\Repositories\RoleRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RoleIndexApiTest extends TestCase
{
    use RefreshDatabase;

    private $api = 'api/roles';

    protected RoleRepository $roleRepo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->roleRepo = new RoleRepository();
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
        $names = $this->roleRepo->all()->pluck(Role::NAME)->toArray();
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
