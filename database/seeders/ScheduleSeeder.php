<?php

namespace Database\Seeders;

use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use App\Modules\Schedule\Models\ScheduleHour;

class ScheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ScheduleHour::truncate();

        $scheduleHours = [];
        $startTime = new \DateTime('08:00:00');
        $endTime = new \DateTime('16:00:00');
        $interval = new \DateInterval('PT15M'); // 15 min

        while ($startTime < $endTime) {
            $end = clone $startTime;
            $end->add($interval);

            $scheduleHours[] = [
                'id' => (string) Str::uuid(),
                'start' => $startTime->format('H:i:s'),
                'end' => $end->format('H:i:s'),
                'hour' => $startTime->format('H'),
                'created_at' => now(),
            ];

            $startTime->add($interval);
        }

        ScheduleHour::insert($scheduleHours);

        $this->command->info(self::class . ' is finished');
    }
}
