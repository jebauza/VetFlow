<?php

namespace App\Modules\Schedule\Requests;

use App\Common\Requests\ApiRequest;

class StoreScheduleRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'date' => 'required|date_format:Y-m-d',
            'user_id' => 'required|uuid',
            'schedule_hour_ids' => 'required|array|min:1',
            'schedule_hour_ids.*' => 'uuid'
        ];
    }
}
