<?php

namespace Tests\Feature\Api\Schedule;

use Tests\Feature\Api\ApiTestCase;
use App\Modules\Role\Models\Role;
use App\Modules\Schedule\Models\ScheduleHour;
use App\Modules\Schedule\Repositories\ScheduleHourRepository;
use Database\Seeders\ScheduleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ScheduleConfigApiTest extends ApiTestCase
{
    use RefreshDatabase;

    private string $api = 'api/veterinarians/schedules/config';
    private string $token;
    protected ScheduleHourRepository $scheduleHourRepo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ScheduleSeeder::class);

        $this->scheduleHourRepo = new ScheduleHourRepository(new ScheduleHour);

        $this->token = $this->getAccessToken($this->superAdmin());
    }

    public function test_config_unauthorized_401(): void
    {
        $this->assertEndpointRequiresAuth(self::GET, $this->api);
    }

    public function test_config_200(): void
    {
        $veterinaryRole = Role::findByName(Role::VET_NAME);
        $veterinaryRole->givePermissionTo('veterinary.register');

        $totalHours = $this->scheduleHourRepo->all()
            ->groupBy(ScheduleHour::HOUR)
            ->count();

        $this->withHeaders(['Authorization' => "Bearer {$this->token}"])
            ->getJson($this->api)
            ->assertOk()
            ->assertJsonStructure([
                'message',
                'data' => [
                    'roles' => ['*' => ['id', 'name']],
                    'hours' => [
                        '*' => [
                            'hour',
                            'segments' => ['*' => ['id', 'start', 'end']],
                        ],
                    ],
                ],
            ])
            ->assertJsonPath('message', __('OK'))
            ->assertJsonCount(1, 'data.roles')
            ->assertJsonPath('data.roles.0.name', Role::VET_NAME)
            ->assertJsonCount($totalHours, 'data.hours');
    }
}
