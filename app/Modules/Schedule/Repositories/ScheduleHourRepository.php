<?php

namespace App\Modules\Schedule\Repositories;

use App\Common\Repositories\BaseRepository;
use App\Modules\Schedule\Models\ScheduleDay;
use Illuminate\Database\Eloquent\Collection;
use App\Modules\Schedule\Models\ScheduleHour;

class ScheduleHourRepository extends BaseRepository
{
    public function __construct(ScheduleHour $model)
    {
        parent::__construct($model);
    }

    public function all(): Collection
    {
        return ScheduleHour::orderBy(ScheduleHour::START)->get();
    }
}
