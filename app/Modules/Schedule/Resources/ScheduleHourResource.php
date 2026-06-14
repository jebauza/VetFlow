<?php

namespace App\Modules\Schedule\Resources;

use Illuminate\Http\Request;
use App\Modules\Schedule\Models\ScheduleHour;
use Illuminate\Http\Resources\Json\JsonResource;

class ScheduleHourResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        parent::wrap(null);

        return [
            'id' => $this->{ScheduleHour::ID},
            'start' => $this->{ScheduleHour::START},
            'end' => $this->{ScheduleHour::END},
            'hour' => $this->{ScheduleHour::HOUR},
        ];
    }
}
