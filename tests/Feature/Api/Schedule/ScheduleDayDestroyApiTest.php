<?php

namespace Tests\Feature\Api\Schedule;

use Illuminate\Support\Str;
use Tests\Feature\Api\ApiTestCase;
use App\Modules\Schedule\Models\ScheduleDay;
use App\Modules\Schedule\Models\ScheduleHour;
use App\Modules\Schedule\Repositories\ScheduleDayRepository;
use App\Modules\Schedule\Repositories\ScheduleHourRepository;
use App\Modules\User\Models\User;
use Database\Seeders\ScheduleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ScheduleDayDestroyApiTest extends ApiTestCase
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
    }

    private function url(string $id): string
    {
        return str_replace(':id', $id, $this->api);
    }

    public function test_destroy_unauthorized_401(): void
    {
        $this->assertEndpointRequiresAuth(
            self::DELETE,
            $this->url($this->scheduleDay->{ScheduleDay::ID})
        );
    }

    public function test_destroy_200(): void
    {
        $this->withHeaders(['Authorization' => "Bearer {$this->token}"])
            ->deleteJson($this->url($this->scheduleDay->{ScheduleDay::ID}))
            ->assertOk()
            ->assertJson(['message' => __('Deleted successfully')]);

        $this->assertDatabaseMissing(ScheduleDay::TABLE, [
            ScheduleDay::ID => $this->scheduleDay->{ScheduleDay::ID},
        ]);
    }

    public function test_destroy_not_found_404(): void
    {
        $this->assertEndpointReturnsNotFound(
            self::DELETE,
            $this->url(Str::uuid()->toString()),
            $this->token
        );
    }

    public function test_destroy_validation_422(): void
    {
        $this->withHeaders(['Authorization' => "Bearer {$this->token}"])
            ->deleteJson($this->url('invalid-uuid'))
            ->assertStatus(422)
            ->assertJsonPath('message', __('Validation errors'))
            ->assertJsonStructure([
                'message',
                'errors' => ['userId'],
            ]);
    }
}
