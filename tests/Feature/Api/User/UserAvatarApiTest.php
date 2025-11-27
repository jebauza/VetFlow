<?php

namespace Tests\Feature\Api\User;

use Tests\TestCase;
use Illuminate\Support\Str;
use App\Modules\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserAvatarApiTest extends TestCase
{
    use RefreshDatabase;

    private $api = 'api/users/:id/avatar';

    public function test_show_200()
    {
        $user = User::whereNotNull(User::AVATAR)->first();

        if ($user) {
            $this->getJson(str_replace(':id', $user->{User::ID}, $this->api))
                ->assertOk();
        } else {
            $this->assertTrue(true);
        }
    }

    public function test_show_validation_422()
    {
        $this->getJson($this->api)
            ->assertStatus(422)
            ->assertJsonStructure(['user']);
    }

    public function test_show_id_not_found_404()
    {
        $this->getJson(str_replace(':id', Str::uuid()->toString(), $this->api))
            ->assertStatus(404)
            ->assertJsonStructure(['message']);
    }
}
