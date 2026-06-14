<?php

namespace App\Modules\Schedule\Services;

use Illuminate\Support\Collection;
use App\Modules\Role\Services\RoleService;
use App\Modules\Schedule\DTOs\Outputs\ConfigDTO;
use App\Modules\Schedule\DTOs\Outputs\HourItemDTO;
use App\Modules\Schedule\Models\ScheduleHour;
use App\Modules\Schedule\Repositories\ScheduleDayRepository;
use App\Modules\Schedule\Repositories\ScheduleHourRepository;
use App\Modules\User\Services\UserService;

class ScheduleService
{
    public function __construct(
        protected readonly RoleService $roleService,

        protected readonly ScheduleHourRepository $scheduleHourRepo,
    ) {}

    public function config(): ConfigDTO
    {
        $roles = $this->roleService->veterinaryRoles();
        $hourItems = $this->scheduleHourRepo->all()
            ->groupBy(ScheduleHour::HOUR)
            ->map(function (Collection $scheduleHours, int $hour) {
                return new HourItemDTO(
                    $hour,
                    $scheduleHours
                );
            })
            ->values();

        return new ConfigDTO($roles, $hourItems);
    }
}
