<?php

namespace App\Modules\Schedule\Repositories;

use App\Common\Repositories\BaseRepository;
use App\Modules\Schedule\Models\ScheduleDay;
use App\Modules\Schedule\Models\ScheduleHour;

class ScheduleDayRepository extends BaseRepository
{
    public function __construct(ScheduleDay $model)
    {
        parent::__construct($model);
    }

    public function syncHours(string $userId, string $date, array $hourIds): ScheduleDay
    {
        $day = ScheduleDay::updateOrCreate([
            ScheduleDay::USER_ID => $userId,
            ScheduleDay::DATE => $date
        ]);

        $day->hours()->sync($hourIds);

        return $day->load('hours');
    }

    public function test() {}
}
