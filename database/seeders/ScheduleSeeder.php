<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use App\Modules\Schedule\Models\ScheduleHour;

class ScheduleSeeder extends Seeder
{
    private const START = '08:00:00';
    private const END   = '16:00:00';
    private const INTERVAL_MINUTES = 15;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ScheduleHour::truncate();

        $current  = Carbon::createFromTimeString(self::START);
        $endTime  = Carbon::createFromTimeString(self::END);
        $now      = now();

        $scheduleHours = [];

        while ($current->lt($endTime)) {
            $next = $current->copy()->addMinutes(self::INTERVAL_MINUTES);

            $scheduleHours[] = [
                'id'         => (string) Str::uuid(),
                'start'      => $current->toTimeString(),
                'end'        => $next->toTimeString(),
                'hour'       => $current->format('H'),
                'created_at' => $now,
            ];

            $current->addMinutes(self::INTERVAL_MINUTES);
        }

        ScheduleHour::insert($scheduleHours);

        $this->command->info(self::class . ' is finished');
    }
}
