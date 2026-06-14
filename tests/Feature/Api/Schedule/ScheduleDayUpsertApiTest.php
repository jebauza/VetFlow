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

class ScheduleDayUpsertApiTest extends ApiTestCase
{
    use RefreshDatabase;

    private string $api = 'api/veterinarians/schedules/day/upsert';
    private string $token;
    private array $payload;
    protected ScheduleDayRepository $scheduleDayRepo;
    protected ScheduleHourRepository $scheduleHourRepo;
    protected User $veterinary;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ScheduleSeeder::class);

        $this->scheduleDayRepo  = new ScheduleDayRepository(new ScheduleDay);
        $this->scheduleHourRepo = new ScheduleHourRepository(new ScheduleHour);

        $this->token = $this->getAccessToken($this->superAdmin());

        $this->veterinary = User::factory()->create();
        $this->veterinary->givePermissionTo('veterinary.register');

        $this->payload = [
            'user_id'           => $this->veterinary->{User::ID},
            'date'              => '2026-06-20',
            'schedule_hour_ids' => $this->scheduleHourRepo->random(2)
                ->pluck(ScheduleHour::ID)
                ->toArray(),
        ];
    }

    public function test_upsert_unauthorized_401(): void
    {
        $this->assertEndpointRequiresAuth(self::POST, $this->api, $this->payload);
    }

    public function test_upsert_creates_200(): void
    {
        $response = $this->withHeaders(['Authorization' => "Bearer {$this->token}"])
            ->postJson($this->api, $this->payload)
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
            ->assertJsonPath('data.user_id', $this->payload['user_id'])
            ->assertJsonPath('data.date', $this->payload['date'])
            ->assertJsonCount(count($this->payload['schedule_hour_ids']), 'data.hours');

        $this->assertDatabaseHas(ScheduleDay::TABLE, [
            ScheduleDay::ID      => $response->json('data.id'),
            ScheduleDay::USER_ID => $this->payload['user_id'],
        ]);

        $scheduleDay = $this->scheduleDayRepo->findOrFailWithRelations(
            $response->json('data.id'),
            ['hours']
        );

        $this->assertEqualsCanonicalizing(
            $this->payload['schedule_hour_ids'],
            $scheduleDay->hours->pluck(ScheduleHour::ID)->toArray()
        );

        $data = json_decode((new ScheduleDayResource($scheduleDay))->toJson(), true);
        $response->assertJsonPath('data', $data);
    }

    public function test_upsert_updates_existing_200(): void
    {
        $this->withHeaders(['Authorization' => "Bearer {$this->token}"])
            ->postJson($this->api, $this->payload);

        $newHourIds = $this->scheduleHourRepo->random(3)
            ->pluck(ScheduleHour::ID)
            ->toArray();

        $response = $this->withHeaders(['Authorization' => "Bearer {$this->token}"])
            ->postJson($this->api, array_merge($this->payload, ['schedule_hour_ids' => $newHourIds]))
            ->assertOk()
            ->assertJsonPath('data.date', $this->payload['date'])
            ->assertJsonCount(count($newHourIds), 'data.hours');

        $scheduleDay = $this->scheduleDayRepo->findOrFailWithRelations(
            $response->json('data.id'),
            ['hours']
        );

        $this->assertEqualsCanonicalizing(
            $newHourIds,
            $scheduleDay->hours->pluck(ScheduleHour::ID)->toArray()
        );

        $this->assertDatabaseCount(ScheduleDay::TABLE, 1);
    }

    public function test_upsert_user_not_veterinary_404(): void
    {
        $nonVeterinary = User::factory()->create();

        $data = $this->payload;
        $data['user_id'] = $nonVeterinary->{User::ID};

        $this->withHeaders(['Authorization' => "Bearer {$this->token}"])
            ->postJson($this->api, $data)
            ->assertNotFound();
    }

    public function test_upsert_schedule_hour_not_found_404(): void
    {
        $data = $this->payload;
        $data['schedule_hour_ids'] = [Str::uuid()->toString()];

        $this->withHeaders(['Authorization' => "Bearer {$this->token}"])
            ->postJson($this->api, $data)
            ->assertNotFound();
    }

    public function test_upsert_validation_422(): void
    {
        // Required fields missing
        $this->withHeaders(['Authorization' => "Bearer {$this->token}"])
            ->postJson($this->api)
            ->assertStatus(422)
            ->assertJsonPath('message', __('Validation errors'))
            ->assertJsonStructure([
                'message',
                'errors' => ['date', 'user_id', 'schedule_hour_ids'],
            ]);

        // Invalid date format
        $data = $this->payload;
        $data['date'] = '20-06-2026';
        $this->withHeaders(['Authorization' => "Bearer {$this->token}"])
            ->postJson($this->api, $data)
            ->assertStatus(422)
            ->assertJsonStructure(['message', 'errors' => ['date']]);

        // user_id not a UUID
        $data = $this->payload;
        $data['user_id'] = 'not-a-uuid';
        $this->withHeaders(['Authorization' => "Bearer {$this->token}"])
            ->postJson($this->api, $data)
            ->assertStatus(422)
            ->assertJsonStructure(['message', 'errors' => ['user_id']]);

        // schedule_hour_ids not an array
        $data = $this->payload;
        $data['schedule_hour_ids'] = 'not-an-array';
        $this->withHeaders(['Authorization' => "Bearer {$this->token}"])
            ->postJson($this->api, $data)
            ->assertStatus(422)
            ->assertJsonStructure(['message', 'errors' => ['schedule_hour_ids']]);

        // schedule_hour_ids empty array
        $data = $this->payload;
        $data['schedule_hour_ids'] = [];
        $this->withHeaders(['Authorization' => "Bearer {$this->token}"])
            ->postJson($this->api, $data)
            ->assertStatus(422)
            ->assertJsonStructure(['message', 'errors' => ['schedule_hour_ids']]);

        // schedule_hour_ids.* not UUID
        $data = $this->payload;
        $data['schedule_hour_ids'] = ['not-a-uuid'];
        $this->withHeaders(['Authorization' => "Bearer {$this->token}"])
            ->postJson($this->api, $data)
            ->assertStatus(422)
            ->assertJsonStructure(['message', 'errors' => ['schedule_hour_ids.0']]);
    }
}
