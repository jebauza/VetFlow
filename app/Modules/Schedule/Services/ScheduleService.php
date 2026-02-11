<?php

namespace App\Modules\Schedule\Services;

use Illuminate\Support\Collection;
use App\Modules\Schedule\DTOs\ConfigDTO;
use App\Modules\Schedule\DTOs\HourItemDTO;
use App\Modules\Role\Repositories\RoleRepository;
use App\Modules\Schedule\Models\ScheduleHour;
use App\Modules\Schedule\Repositories\ScheduleDayRepository;
use App\Modules\Schedule\Repositories\ScheduleHourRepository;

class ScheduleService
{
    public function __construct(
        protected readonly ScheduleDayRepository $scheduleDayRepo,
        protected readonly ScheduleHourRepository $scheduleHourRepo,
        protected readonly RoleRepository $roleRepo
    ) {}

    public function config(): ConfigDTO
    {
        $roles = $this->roleRepo->all();
        $hourItems = $this->scheduleHourRepo->all()
            ->groupBy(ScheduleHour::HOUR)
            ->map(function (Collection $scheduleHours, int $key) {
                return new HourItemDTO(
                    $key,
                    $scheduleHours
                );
            })
            ->values();

        return new ConfigDTO($roles, $hourItems);
    }
}
