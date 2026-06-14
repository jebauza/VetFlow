<?php

namespace Tests\Feature\Api\Veterinary;

use Tests\Feature\Api\ApiTestCase;
use App\Modules\User\Models\User;
use App\Modules\User\Resources\UserResource;
use App\Modules\User\Repositories\UserRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;

class VeterinaryIndexApiTest extends ApiTestCase
{
    use RefreshDatabase;

    private string $api = 'api/veterinarians';
    private string $token;
    protected UserRepository $userRepo;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userRepo = new UserRepository(new User);

        $veterinaries = User::factory(3)->create();
        foreach ($veterinaries as $user) {
            $user->givePermissionTo('veterinary.register');
        }

        $this->token = $this->getAccessToken($this->superAdmin());
    }

    public function test_index_unauthorized_401(): void
    {
        $this->assertEndpointRequiresAuth(self::GET, $this->api);
    }

    public function test_index_200(): void
    {
        $response = $this->withHeaders(['Authorization' => "Bearer {$this->token}"])
            ->getJson($this->api)
            ->assertOk()
            ->assertJsonStructure([
                'message',
                'data' => [
                    '*' => [
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
                ],
            ])
            ->assertJsonPath('message', __('OK'));

        $veterinaries = $this->userRepo->searchVeterinaries(null, true);
        $data = json_decode(UserResource::collection($veterinaries)->toJson(), true);

        $response->assertJsonCount($veterinaries->count(), 'data')
            ->assertJsonPath('data', $data);
    }

    public function test_index_search_200(): void
    {
        $search = 'a';
        $total  = $this->userRepo->searchVeterinaries($search)->count();

        $this->withHeaders(['Authorization' => "Bearer {$this->token}"])
            ->getJson("{$this->api}?search={$search}")
            ->assertOk()
            ->assertJsonPath('message', __('OK'))
            ->assertJsonCount($total, 'data');
    }
}
