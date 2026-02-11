<?php

namespace App\Modules\Schedule\DTOs;

use Illuminate\Support\Collection;
use App\Modules\Schedule\Models\ScheduleHour;

readonly class HourItemDTO
{
    const HOUR = 'hour';
    const SCHEDULE_HOURS = 'scheduleHours';

    /**
     * @param int $hour
     * @param Collection<int, ScheduleHour> $scheduleHours
     */
    public function __construct(
        public int $hour,
        public Collection $scheduleHours
    ) {}
}
