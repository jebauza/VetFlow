<?php

namespace Tests\Feature\Api\Auth;

use Tests\Feature\Api\ApiTestCase;
use Illuminate\Support\Str;
use App\Modules\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthRegisterApiTest extends ApiTestCase
{
    use RefreshDatabase;

    private $api = 'api/auth/register';
    private array $payload = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->payload = [
            'name' => 'Test',
            'surname' => 'Test',
            'email' => 'test@gmail.com',
            'password' => 'test123456789',
        ];
    }

    public function test_register_201(): void
    {
        $response = $this->postJson($this->api, $this->payload)
            ->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'access_token',
                    'token_type',
                    'expires_in',
                    'expires_at',
                    'user' => [
                        'id',
                        'name',
                        'email',
                    ]
                ]
            ])
            ->assertJsonPath('message', __('User registered successfully'));

        // Database verification
        $this->assertDatabaseHas(User::TABLE, [
            User::ID => $response->json('data.user.id'),
            User::EMAIL => $this->payload['email'],
            User::NAME => $this->payload['name'],
            User::SURNAME => $this->payload['surname'],
        ]);

        // Verify that the password field was not saved as plain text
        $this->assertDatabaseMissing(User::TABLE, [
            User::EMAIL => $this->payload['email'],
            User::PASSWORD => $this->payload['password'],
        ]);
    }

    public function test_register_validation_422(): void
    {
        // Data required
        $this->postJson($this->api, [])
            ->assertStatus(422)
            ->assertJsonPath('message', __('Validation errors'))
            ->assertJsonStructure([
                'message',
                'errors' => ['email', 'name', 'surname', 'password'],
            ]);

        // Data string
        $data = $this->payload;
        $data['name'] = 1;
        $data['surname'] = 2;
        $data['password'] = 10000000000000;
        $this->postJson($this->api, $data)
            ->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => ['name', 'surname', 'password'],
            ]);

        // Data min and max
        $data = $this->payload;
        $data['name'] = Str::random(256);
        $data['surname'] = Str::random(256);
        $data['password'] = Str::random(7);
        $this->postJson($this->api, $data)
            ->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => ['name', 'surname', 'password'],
            ]);

        // Invalid email
        $data = $this->payload;
        $data['email'] = 'invalid_email';
        $this->postJson($this->api, $data)
            ->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => ['email'],
            ]);

        // Unique email
        $user = User::factory()->create();
        $data = $this->payload;
        $data['email'] = $user->{User::EMAIL};
        $this->postJson($this->api, $data)
            ->assertStatus(422)
            ->assertJson([
                'message' => __('Validation errors'),
                'errors' => [
                    'email' => ['The email has already been taken.']
                ],
            ]);
    }
}
