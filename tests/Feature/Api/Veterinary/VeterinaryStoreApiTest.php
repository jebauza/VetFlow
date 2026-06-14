<?php

namespace Tests\Feature\Api\Veterinary;

use Tests\Feature\Api\ApiTestCase;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use App\Modules\Role\Models\Role;
use App\Modules\User\Models\User;
use App\Modules\User\Resources\UserResource;
use App\Modules\User\Repositories\UserRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;

class VeterinaryStoreApiTest extends ApiTestCase
{
    use RefreshDatabase;

    private string $api = 'api/veterinarians';
    private string $token;
    private array $payload;
    protected UserRepository $userRepo;
    protected Role $veterinaryRole;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userRepo = new UserRepository(new User);

        $this->veterinaryRole = Role::findByName(Role::VET_NAME);
        $this->veterinaryRole->givePermissionTo('veterinary.register');

        $this->payload = [
            'email'         => 'vet@clinic.com',
            'name'          => 'Jane',
            'surname'       => 'Doe',
            'password'      => 'Password123!',
            'phone'         => '622788616',
            'type_document' => Arr::random(User::TYPE_DOCUMENT_VALUES),
            'n_document'    => Str::random(10),
            'birth_date'    => '1990-05-15',
            'designation'   => 'Veterinarian',
            'gender'        => Arr::random(User::GENDER_VALUES),
            'role_id'       => $this->veterinaryRole->{Role::ID},
        ];

        $this->token = $this->getAccessToken($this->superAdmin());
    }

    public function test_store_unauthorized_401(): void
    {
        $this->assertEndpointRequiresAuth(self::POST, $this->api, $this->payload);
    }

    public function test_store_201(): void
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
                    'roles'           => ['*' => ['id', 'name']],
                    'all_permissions' => ['*' => ['id', 'name']],
                ],
            ])
            ->assertJsonPath('message', __('Created'))
            ->assertJsonPath('data.email', $this->payload['email'])
            ->assertJsonPath('data.name', $this->payload['name'])
            ->assertJsonPath('data.surname', $this->payload['surname']);

        $this->assertDatabaseHas(User::TABLE, [
            User::ID      => $response->json('data.id'),
            User::EMAIL   => $this->payload['email'],
            User::NAME    => $this->payload['name'],
            User::SURNAME => $this->payload['surname'],
        ]);

        $user = $this->userRepo->findWithRelations(
            $response->json('data.id'),
            ['permissions:id,name', 'roles:id,name']
        );

        $this->assertEqualsCanonicalizing(
            [$this->payload['role_id']],
            $user->roles->pluck(Role::ID)->toArray()
        );

        $data = json_decode((new UserResource($user))->toJson(), true);
        $response->assertJsonPath('data', $data);
    }

    public function test_store_role_not_veterinary_404(): void
    {
        $nonVeterinaryRole = Role::findByName(Role::ADMIN_NAME);

        $data             = $this->payload;
        $data['role_id']  = $nonVeterinaryRole->{Role::ID};
        $data['email']    = 'another@clinic.com';

        $this->withHeaders(['Authorization' => "Bearer {$this->token}"])
            ->postJson($this->api, $data)
            ->assertNotFound();
    }

    public function test_store_validation_422(): void
    {
        // Required fields missing
        $this->withHeaders(['Authorization' => "Bearer {$this->token}"])
            ->postJson($this->api)
            ->assertStatus(422)
            ->assertJsonPath('message', __('Validation errors'))
            ->assertJsonStructure([
                'message',
                'errors' => ['email', 'name', 'surname', 'password'],
            ]);

        // Invalid field formats
        $this->withHeaders(['Authorization' => "Bearer {$this->token}"])
            ->postJson($this->api, [
                'email'         => 'invalid_email',
                'name'          => Str::random(300),
                'surname'       => Str::random(300),
                'password'      => 'short',
                'phone'         => Str::random(30),
                'type_document' => 'invalid',
                'n_document'    => Str::random(30),
                'birth_date'    => 'not-a-date',
                'designation'   => Str::random(300),
                'gender'        => 'invalid',
                'role_id'       => 'not-a-uuid',
            ])
            ->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => [
                    'email', 'name', 'surname', 'password',
                    'phone', 'type_document', 'n_document',
                    'birth_date', 'designation', 'gender', 'role_id',
                ],
            ]);

        // Duplicate email
        $data          = $this->payload;
        $data['email'] = $this->userRepo->random()->{User::EMAIL};
        $this->withHeaders(['Authorization' => "Bearer {$this->token}"])
            ->postJson($this->api, $data)
            ->assertStatus(422)
            ->assertJsonStructure(['message', 'errors' => ['email']]);

        // role_id UUID valid but not in DB
        $data            = $this->payload;
        $data['email']   = 'unique@clinic.com';
        $data['role_id'] = Str::uuid()->toString();
        $this->withHeaders(['Authorization' => "Bearer {$this->token}"])
            ->postJson($this->api, $data)
            ->assertStatus(422)
            ->assertJsonStructure(['message', 'errors' => ['role_id']]);
    }
}
