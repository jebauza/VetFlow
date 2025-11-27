<?php

namespace Tests\Feature\Api\User;

use Tests\TestCase;
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

class UserStoreApiTest extends TestCase
{
    use RefreshDatabase;

    private $api = 'api/users';

    protected UserRepository $userRepo;
    protected RoleRepository $roleRepo;
    private array $safeValues = [];


    protected function setUp(): void
    {
        parent::setUp();
        $this->userRepo = new UserRepository(new User);
        $this->roleRepo = new RoleRepository(new Role);

        Storage::fake('public');
        $avatar = UploadedFile::fake()->create('avatar.jpg', 200, 'image/jpeg');
        $roleId = $this->roleRepo->firstRandom()->{Role::ID};

        $this->safeValues = [
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
            "role_id" => $roleId,
        ];
    }

    public function test_store_201()
    {
        $user = $this->superAdmin();
        $token = $this->getAccessToken($user);

        $response = $this->withHeaders(['Authorization' => "Bearer {$token}",])
            ->postJson($this->api, $this->safeValues);

        if ($userId = $response->json('data.id')) {
            $user = $this->userRepo->findWithRelations($userId, ['permissions:id,name', 'roles:id,name']);
            $storeData = json_decode((new UserResource($user))->toJson(), true);
        }

        $response->assertCreated()
            ->assertJsonStructure([
                'message',
                'data',
            ]);

        if (isset($storeData)) {
            $response->assertJson([
                'message' => 'Created successfully',
                'data' => $storeData
            ]);

            $this->assertTrue(Storage::disk('public')->exists($user->{User::AVATAR}));
        }
    }

    public function test_store_with_invalid_token_401()
    {
        $this->withHeaders([
            'Authorization' => 'Bearer invalid_token',
        ])->postJson($this->api)
            ->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthenticated.',
            ]);
    }

    public function test_store_validation_422()
    {
        $user = $this->superAdmin();
        $token = $this->getAccessToken($user);

        // Data required
        $this->withHeaders(['Authorization' => "Bearer {$token}",])
            ->postJson($this->api)
            ->assertStatus(422)
            ->assertJsonStructure([
                'email',
                'name',
                'surname',
                'password',
            ]);

        // Data not valid
        $avatarInvalid = UploadedFile::fake()->create('archivo.pdf', 200);
        $this->withHeaders(['Authorization' => "Bearer {$token}",])
            ->postJson($this->api, [
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
            ]);

        // Data valid but unique email
        $data = $this->safeValues;
        $data['email'] = $this->userRepo->firstRandom()->{User::EMAIL};
        $this->withHeaders(['Authorization' => "Bearer {$token}",])
            ->postJson($this->api, $data)
            ->assertStatus(422)
            ->assertJsonStructure([
                'email',
            ]);

        // Invalid DB role_id
        $data = $this->safeValues;
        $data['role_id'] = Str::uuid()->toString();
        $this->withHeaders(['Authorization' => "Bearer {$token}",])
            ->postJson($this->api, $data)
            ->assertStatus(422)
            ->assertJsonStructure([
                'role_id',
            ]);
    }
}
