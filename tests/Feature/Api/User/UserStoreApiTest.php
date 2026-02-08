<?php

namespace Tests\Feature\Api\User;

use Tests\Feature\Api\ApiTestCase;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use App\Modules\Role\Models\Role;
use App\Modules\User\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use App\Modules\User\Resources\UserResource;
use App\Modules\Role\Repositories\RoleRepository;
use App\Modules\User\Repositories\UserRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserStoreApiTest extends ApiTestCase
{
    use RefreshDatabase;

    private $api = 'api/users';
    private string $token;
    private array $payload = [];
    protected UserRepository $userRepo;
    protected RoleRepository $roleRepo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userRepo = new UserRepository(new User);
        $this->roleRepo = new RoleRepository(new Role);

        Storage::fake('public');
        $avatar = UploadedFile::fake()->create('avatar.jpg', 200, 'image/jpeg');

        $this->payload = [
            "email" => "user@test.com",
            "name" => "Name Test",
            "surname" => "Surname Test",
            "password" => "123456789",
            "avatar" => $avatar,
            "phone" => '622788616',
            "type_document" => Arr::random(User::TYPE_DOCUMENT_VALUES),
            "n_document" => Str::random(10),
            "birth_date" => '1990-01-01',
            "designation" => Str::random(255),
            "gender" => Arr::random(User::GENDER_VALUES),
            "role_id" => $this->roleRepo->random()->{Role::ID},
        ];
        $this->token = $this->getAccessToken(User::factory()->create());
    }

    public function test_store_unauthorized_401()
    {
        $this->assertEndpointRequiresAuth(self::POST, $this->api, $this->payload);
    }

    public function test_store_201()
    {
        $response = $this->withHeaders(['Authorization' => "Bearer {$this->token}"])
            ->postJson($this->api, $this->payload)
            ->assertCreated()
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id',
                    'name',
                    'surname',
                    'email',
                    'avatar',
                    'phone',
                    'type_document',
                    'n_document',
                    'birth_date',
                    'designation',
                    'gender',
                    'roles' => [
                        '*' => [
                            'id',
                            'name',
                        ]
                    ],
                    'all_permissions' => [
                        '*' => [
                            'id',
                            'name',
                        ]
                    ]
                ]
            ])
            ->assertJsonPath('message', __('Created'))
            ->assertJsonPath('data.name', $this->payload['name'])
            ->assertJsonPath('data.surname', $this->payload['surname'])
            ->assertJsonPath('data.email', $this->payload['email']);

        $this->assertDatabaseHas(User::TABLE, [
            User::ID => $response->json('data.id'),
            User::EMAIL => $this->payload['email'],
            User::NAME => $this->payload['name'],
            User::SURNAME => $this->payload['surname'],
            User::PHONE => $this->payload['phone'],
            User::TYPE_DOCUMENT => $this->payload['type_document'],
            User::N_DOCUMENT => $this->payload['n_document'],
            User::BIRTH_DATE => $this->payload['birth_date'],
            User::DESIGNATION => $this->payload['designation'],
            User::GENDER => $this->payload['gender'],
        ]);

        $this->assertDatabaseMissing(User::TABLE, [
            User::EMAIL => $this->payload['email'],
            User::PASSWORD => $this->payload['password'], // Password should be hashed, so the raw value must not exist in DB
        ]);

        $user = $this->userRepo->findWithRelations(
            $response->json('data.id'),
            ['permissions:id,name', 'roles:id,name']
        );

        $this->assertTrue(Storage::disk('public')->exists($user->{User::AVATAR}));
        $this->assertEqualsCanonicalizing(
            [$this->payload['role_id']],
            $user->roles->pluck(Role::ID)->toArray()
        );

        $data = json_decode((new UserResource($user))->toJson(), true);
        $response->assertJsonPath('data', $data);
    }

    public function test_store_validation_422()
    {
        // Data required
        $this->withHeaders(['Authorization' => "Bearer {$this->token}"])
            ->postJson($this->api)
            ->assertStatus(422)
            ->assertJsonPath('message', __('Validation errors'))
            ->assertJsonStructure([
                'message',
                'errors' => ['email', 'name', 'surname', 'password'],
            ]);

        // Data not valid
        $avatarInvalid = UploadedFile::fake()->create('archivo.pdf', 200);
        $this->postJson($this->api, [
            "email" => "invalid_email",
            "name" => Str::random(300),
            "surname" => Str::random(300),
            "password" => "short",
            "avatar" => $avatarInvalid,
            "phone" => Str::random(30),
            "type_document" => 'invalid_type_document',
            "n_document" => Str::random(30),
            "birth_date" => Str::random(10),
            "designation" => Str::random(300),
            "gender" => 'invalid_gender',
            "role_id" => 'invalid_uuid',
        ])
            ->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => [
                    'email',
                    'name',
                    'surname',
                    'password',
                    "avatar",
                    "phone",
                    "type_document",
                    "n_document",
                    "birth_date",
                    "designation",
                    "gender",
                    "role_id",
                ],
            ]);

        // Data valid but unique email
        $data = $this->payload;
        $data['email'] = $this->userRepo->random()->{User::EMAIL};
        $this->postJson($this->api, $data)
            ->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => ['email'],
            ]);

        // Invalid DB role_id
        $data = $this->payload;
        $data['role_id'] = Str::uuid()->toString();
        $this->postJson($this->api, $data)
            ->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => ['role_id'],
            ]);
    }
}
