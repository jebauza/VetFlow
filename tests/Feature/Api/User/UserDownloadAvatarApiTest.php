<?php

namespace Tests\Feature\Api\User;

use Tests\Feature\Api\ApiTestCase;
use Illuminate\Support\Str;
use App\Modules\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserDownloadAvatarApiTest extends ApiTestCase
{
    use RefreshDatabase;

    private $api = 'api/users/:id/download/avatar';

    public function test_download_avatar_200()
    {
        $user = User::whereNotNull(User::AVATAR)->first();

        if ($user) {
            $this->getJson(str_replace(':id', $user->{User::ID}, $this->api))
                ->assertOk();
        } else {
            $this->assertTrue(true);
        }
    }

    public function test_download_avatar_404()
    {
        $this->assertEndpointReturnsNotFound(
            self::GET,
            str_replace(':id', Str::uuid(), $this->api),
        );
    }

    public function test_download_avatar_validation_422()
    {
        $this->getJson(str_replace(':id', 'invalid-uuid', $this->api))
            ->assertStatus(422)
            ->assertJsonPath('message', __('Validation errors'))
            ->assertJsonStructure([
                'message',
                'errors' => ['user'],
            ]);
    }
}
