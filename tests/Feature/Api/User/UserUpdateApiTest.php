<?php

namespace Tests\Feature\Api\User;

use Tests\TestCase;
use Illuminate\Support\Str;
use App\Modules\Role\Models\Role;
use App\Modules\User\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use App\Modules\User\Resources\UserResource;
use App\Modules\Role\Repositories\RoleRepository;
use App\Modules\User\Repositories\UserRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserUpdateApiTest extends TestCase
{
    use RefreshDatabase;

    private $api = 'api/users/:id';

    protected UserRepository $userRepo;
    protected RoleRepository $roleRepo;
    private array $safeValues = [];
    private string $userId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userRepo = new UserRepository(new User);
        $this->roleRepo = new RoleRepository(new Role);
        $this->userId = $this->userRepo->firstRandom()->{User::ID};

        Storage::fake('public');
        $avatar = UploadedFile::fake()->create('avatar.jpg', 200, 'image/jpeg');
        $roleId = $this->roleRepo->firstRandom()->{Role::ID};

        $this->safeValues = [
            "email" => "user@test.com",
            "name" => "Name Test",
            "surname" => "Surname Test",
            "password" => "123456789",
            "avatar" => $avatar,

            "role_id" => $roleId,
        ];
    }

    public function test_update_200()
    {
        $user = $this->superAdmin();
        $token = $this->getAccessToken($user);

        // ✅ Create user WITH avatar correctly
        $createResponse = $this->withHeaders(['Authorization' => "Bearer {$token}"])
            ->postJson('api/users', [
                "email" => "user2@test.com",
                "name" => "Name2 Test2",
                "surname" => "Surname2 Test2",
                "password" => "123456789-2",
                "avatar" => $this->safeValues['avatar'],
            ]);

        $user = $this->userRepo->findOrFail($createResponse->json('data.id'));

        // ✅ Update using POST + _method=PUT so avatar works
        Storage::fake('public');
        $avatar2 = UploadedFile::fake()->create('avatar2.jpg', 200, 'image/jpeg');
        $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])
            ->putJson(
                str_replace(':id', $user->{User::ID}, $this->api),
                array_merge($this->safeValues, [/* '_method' => 'PUT', */'avatar' => $avatar2])
            );

        if ($userId = $response->json('data.id')) {
            $updateUser = $this->userRepo->findWithRelations($userId, ['permissions:id,name', 'roles:id,name']);
            $updateData = json_decode((new UserResource($updateUser))->toJson(), true);
        }

        $response->assertOk()
            ->assertJsonStructure([
                'message',
                'data',
            ]);

        if (isset($updateData)) {
            $response->assertJson([
                'message' => 'Updated successfully',
                'data' => $updateData,
            ]);

            // ✅ New avatar exists
            $this->assertTrue(Storage::disk('public')->exists($updateUser->{User::AVATAR}));

            // ✅ Previous avatar deleted
            if ($user->{User::AVATAR}) {
                $this->assertFalse(Storage::disk('public')->exists($user->{User::AVATAR}));
            }
        }
    }

    public function test_update_with_invalid_token_401()
    {
        $this->withHeaders([
            'Authorization' => 'Bearer invalid_token',
        ])->putJson($this->api)
            ->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthenticated.',
            ]);
    }

    public function test_update_validation_422()
    {
        $user = $this->superAdmin();
        $token = $this->getAccessToken($user);

        // Data required
        $this->withHeaders(['Authorization' => "Bearer {$token}",])
            ->putJson(str_replace(':id', $this->userId, $this->api))
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
            ->putJson(str_replace(':id', $this->userId, $this->api), [
                "email" => "invalid_email",
                "name" => Str::random(300),
                "surname" => Str::random(300),
                "password" => "short",
                "avatar" => $avatarInvalid,
                "role_id" => 'invalid_uuid',
            ])
            ->assertStatus(422)
            ->assertJsonStructure([
                'email',
                'name',
                'surname',
                'password',
                "avatar",
                "role_id",
            ]);

        // Data valid but unique email
        $data = $this->safeValues;
        $data['email'] = $this->userRepo->whereNotIn(User::ID, [$this->userId])->first()->{User::EMAIL};
        $this->withHeaders(['Authorization' => "Bearer {$token}",])
            ->putJson(str_replace(':id', $this->userId, $this->api), $data)
            ->assertStatus(422)
            ->assertJsonStructure([
                'email',
            ]);

        // Invalid DB role_id
        $data = $this->safeValues;
        $data['role_id'] = Str::uuid()->toString();
        $this->withHeaders(['Authorization' => "Bearer {$token}",])
            ->putJson(str_replace(':id', $this->userId, $this->api), $data)
            ->assertStatus(422)
            ->assertJsonStructure([
                'role_id',
            ]);
    }
}
