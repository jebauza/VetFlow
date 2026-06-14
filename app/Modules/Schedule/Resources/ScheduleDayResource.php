<?php

namespace App\Modules\Schedule\Resources;

use Illuminate\Http\Request;
use App\Modules\Schedule\Models\ScheduleDay;
use Illuminate\Http\Resources\Json\JsonResource;

class ScheduleDayResource extends JsonResource
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
            'id' => $this->{ScheduleDay::ID},
            'user_id' => $this->{ScheduleDay::USER_ID},
            'date' => $this->{ScheduleDay::DATE}->toDateString(),
            'hours' => ScheduleHourResource::collection(
                $this->whenLoaded('hours')
            ),
        ];
    }
}
