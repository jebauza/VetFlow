<?php

namespace Tests\Feature\Api\Role;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RoleIndexApiTest extends TestCase
{
    use RefreshDatabase;

    private $api = 'api/roles';

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
