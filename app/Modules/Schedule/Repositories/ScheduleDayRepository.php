<?php

namespace App\Modules\Schedule\Repositories;

use App\Common\Helpers\DbExceptionHelper;
use App\Common\Repositories\BaseRepository;
use App\Modules\Schedule\Models\ScheduleDay;
use Illuminate\Database\QueryException;

class ScheduleDayRepository extends BaseRepository
{
    public function __construct(ScheduleDay $model)
    {
        parent::__construct($model);
    }

    /**
     * @param ScheduleDay|string $scheduleDay
     * @param string[] $hourIds
     */
    public function assignHours(ScheduleDay|string $scheduleDay, array $hourIds): ScheduleDay
    {
        if (is_string($scheduleDay)) {
            $scheduleDay = $this->findOrFail($scheduleDay);
        }

        try {
            $scheduleDay->hours()->attach($hourIds);
            return $scheduleDay;
        } catch (QueryException $e) {
            DbExceptionHelper::handle(static::class, $e);
        }
    }

    /**
     * @param ScheduleDay|string $scheduleDay
     * @param string[] $hourIds
     */
    public function syncHours(ScheduleDay|string $scheduleDay, array $hourIds): ScheduleDay
    {
        if (is_string($scheduleDay)) {
            $scheduleDay = $this->findOrFail($scheduleDay);
        }

        try {
            $scheduleDay->hours()->sync($hourIds);
            return $scheduleDay;
        } catch (QueryException $e) {
            DbExceptionHelper::handle(static::class, $e);
        }
    }
}
