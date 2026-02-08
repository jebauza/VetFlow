<?php

namespace Tests\Feature\Api\User;

use Illuminate\Support\Str;
use App\Modules\User\Models\User;
use Tests\Feature\Api\ApiTestCase;
use Illuminate\Database\Eloquent\Collection;
use App\Modules\User\Repositories\UserRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserDestroyApiTest extends ApiTestCase
{
    use RefreshDatabase;

    private $api = 'api/users/:id';
    private string $token;
    protected UserRepository $userRepo;
    protected Collection $users;
    protected User $userAuth;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userRepo = new UserRepository(new User);

        $this->users = User::factory(2)->create();
        $this->userAuth = $this->users->first();
        $this->token = $this->getAccessToken($this->userAuth);
    }

    public function test_destroy_unauthorized_401()
    {
        $this->assertEndpointRequiresAuth(
            self::DELETE,
            str_replace(':id', $this->userAuth->{User::ID}, $this->api)
        );
    }

    public function test_destroy_200()
    {
        $userDeleted = $this->users->last();

        $this->withHeaders(['Authorization' => "Bearer {$this->token}"])
            ->deleteJson(str_replace(':id', $userDeleted->{User::ID}, $this->api))
            ->assertOk()
            ->assertJson(['message' => __('Deleted successfully')]);

        $this->assertSoftDeleted($userDeleted);
    }

    public function test_destroy_404()
    {
        $this->assertEndpointReturnsNotFound(
            self::DELETE,
            str_replace(':id', Str::uuid(), $this->api),
            [],
            $this->token
        );
    }

    public function test_destroy_validation_422(): void
    {
        // Data user_id invalid UUID and required fields
        $this->withHeaders(['Authorization' => "Bearer {$this->token}"])
            ->deleteJson(str_replace(':id', 'invalid-uuid', $this->api))
            ->assertStatus(422)
            ->assertJsonPath('message', __('Validation errors'))
            ->assertJsonStructure([
                'message',
                'errors' => ['user'],
            ]);
    }
}
