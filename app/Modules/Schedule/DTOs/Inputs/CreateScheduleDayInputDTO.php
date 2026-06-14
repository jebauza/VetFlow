<?php

namespace App\Modules\Schedule\DTOs\Inputs;

use Illuminate\Support\Carbon;

class CreateScheduleDayInputDTO
{
    const DATE = 'date';
    const USER_ID = 'user_id';
    const SCHEDULE_HOUR_IDS = 'schedule_hour_ids';

    /**
     * @param string[] $schedule_hour_ids
     */
    public function __construct(
        public readonly Carbon $date,
        public readonly string $user_id,
        public readonly array $schedule_hour_ids
    ) {}

    public function toArray(bool $onlyModel = false): array
    {
        $data = [
            self::DATE              => $this->{self::DATE},
            self::USER_ID              => $this->{self::USER_ID},
        ];

        if (!$onlyModel) {
            $data[self::SCHEDULE_HOUR_IDS] = $this->{self::SCHEDULE_HOUR_IDS};
        }

        return array_filter($data, fn($value) => !is_null($value));
    }
}
