<?php

namespace App\Modules\Schedule\Services;

use App\Modules\Schedule\DTOs\Inputs\CreateScheduleDayInputDTO;
use App\Modules\Schedule\Models\ScheduleDay;
use App\Modules\Schedule\Repositories\ScheduleDayRepository;
use App\Modules\User\Services\VeterinaryService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class ScheduleDayService
{
    public function __construct(
        protected readonly VeterinaryService $veterinaryService,

        protected readonly ScheduleDayRepository $scheduleDayRepo,
    ) {}

    public function findById(string $id): ScheduleDay
    {
        return $this->scheduleDayRepo->findOrFailWithRelations($id, ['hours']);
    }

    public function create(CreateScheduleDayInputDTO $dto): ScheduleDay
    {
        if (!$this->veterinaryService->isVeterinary($dto->user_id)) {
            throw new ModelNotFoundException("The user_id '{$dto->user_id}' is not a veterinary.");
        }

        /** @var ScheduleDay $scheduleDay */
        $scheduleDay = DB::transaction(function () use ($dto): ScheduleDay {
            // DB constraints (unique, foreign key) are handled by the repository layer.

            /** @var ScheduleDay $model */
            $model = $this->scheduleDayRepo->create($dto->toArray(true));
            $model = $this->scheduleDayRepo->assignHours(
                $model,
                $dto->{CreateScheduleDayInputDTO::SCHEDULE_HOUR_IDS}
            );

            return $model;
        });

        return $this->scheduleDayRepo->load($scheduleDay, ['hours']);
    }

    public function upsert(CreateScheduleDayInputDTO $dto): ScheduleDay
    {
        if (!$this->veterinaryService->isVeterinary($dto->user_id)) {
            throw new ModelNotFoundException("The user_id '{$dto->user_id}' is not a veterinary.");
        }

        $scheduleDay = DB::transaction(function () use ($dto): ScheduleDay {
            $model = $this->scheduleDayRepo->updateOrCreate([
                ScheduleDay::USER_ID => $dto->user_id,
                ScheduleDay::DATE => $dto->date
            ]);

            $model = $this->scheduleDayRepo->syncHours(
                $model,
                $dto->{CreateScheduleDayInputDTO::SCHEDULE_HOUR_IDS}
            );

            return $model;
        });

        return $this->scheduleDayRepo->load($scheduleDay, ['hours']);
    }

    public function delete(string $id): void
    {
        $scheduleDay = $this->scheduleDayRepo->findOrFail($id);

        DB::transaction(function () use ($scheduleDay) {
            $this->scheduleDayRepo->delete($scheduleDay->{ScheduleDay::ID});
        });
    }
}
