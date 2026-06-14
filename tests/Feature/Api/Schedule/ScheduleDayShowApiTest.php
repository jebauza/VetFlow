<?php

namespace Tests\Feature\Api\Schedule;

use Illuminate\Support\Str;
use Tests\Feature\Api\ApiTestCase;
use App\Modules\Schedule\Models\ScheduleDay;
use App\Modules\Schedule\Models\ScheduleHour;
use App\Modules\Schedule\Resources\ScheduleDayResource;
use App\Modules\Schedule\Repositories\ScheduleDayRepository;
use App\Modules\Schedule\Repositories\ScheduleHourRepository;
use App\Modules\User\Models\User;
use Database\Seeders\ScheduleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ScheduleDayShowApiTest extends ApiTestCase
{
    use RefreshDatabase;

    private string $api = 'api/veterinarians/schedules/day/:id';
    private string $token;
    protected ScheduleDayRepository $scheduleDayRepo;
    protected ScheduleHourRepository $scheduleHourRepo;
    protected ScheduleDay $scheduleDay;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ScheduleSeeder::class);

        $this->scheduleDayRepo  = new ScheduleDayRepository(new ScheduleDay);
        $this->scheduleHourRepo = new ScheduleHourRepository(new ScheduleHour);

        $this->token = $this->getAccessToken($this->superAdmin());

        $hourIds = $this->scheduleHourRepo->random(2)->pluck(ScheduleHour::ID)->toArray();

        $this->scheduleDay = $this->scheduleDayRepo->create([
            ScheduleDay::USER_ID => $this->superAdmin()->{User::ID},
            ScheduleDay::DATE    => '2026-06-20',
        ]);
        $this->scheduleDayRepo->assignHours($this->scheduleDay, $hourIds);
        $this->scheduleDay = $this->scheduleDayRepo->findOrFailWithRelations(
            $this->scheduleDay->{ScheduleDay::ID},
            ['hours']
        );
    }

    private function url(string $id): string
    {
        return str_replace(':id', $id, $this->api);
    }

    public function test_show_unauthorized_401(): void
    {
        $this->assertEndpointRequiresAuth(self::GET, $this->url($this->scheduleDay->{ScheduleDay::ID}));
    }

    public function test_show_200(): void
    {
        $data = json_decode((new ScheduleDayResource($this->scheduleDay))->toJson(), true);

        $this->withHeaders(['Authorization' => "Bearer {$this->token}"])
            ->getJson($this->url($this->scheduleDay->{ScheduleDay::ID}))
            ->assertOk()
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id',
                    'user_id',
                    'date',
                    'hours' => [
                        '*' => ['id', 'start', 'end', 'hour'],
                    ],
                ],
            ])
            ->assertJsonPath('message', __('OK'))
            ->assertJsonPath('data', $data);
    }

    public function test_show_not_found_404(): void
    {
        $this->withHeaders(['Authorization' => "Bearer {$this->token}"])
            ->getJson($this->url(Str::uuid()->toString()))
            ->assertNotFound();
    }
}
