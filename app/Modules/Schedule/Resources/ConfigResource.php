<?php

namespace App\Modules\Schedule\Resources;

use App\Modules\Role\Resources\RoleLiteResource;
use App\Modules\Schedule\DTOs\Outputs\ConfigDTO;
use App\Modules\Schedule\DTOs\Outputs\HourItemDTO;
use App\Modules\Schedule\Models\ScheduleHour;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use InvalidArgumentException;

/**
 * @property-read ConfigDTO $resource
 */
class ConfigResource extends JsonResource
{
    public function __construct($resource)
    {
        if (!$resource instanceof ConfigDTO) {
            throw new InvalidArgumentException('Expected resource to be an instance of ConfigDTO.');
        }
        parent::__construct($resource);
    }

    public function toArray(Request $request): array
    {
        return [
            'roles' => RoleLiteResource::collection($this->{ConfigDTO::ROLES}),
            'hours' => $this->{ConfigDTO::HOURS}->map(function (HourItemDTO $item) {
                return [
                    'hour' => $item->{HourItemDTO::HOUR},
                    'segments' => $item->{HourItemDTO::SCHEDULE_HOURS}->map(function (ScheduleHour $item) {
                        return [
                            'id' => $item->{ScheduleHour::ID},
                            'start' => $item->{ScheduleHour::START},
                            'end' => $item->{ScheduleHour::END}
                        ];
                    }),
                ];
            }),
        ];
    }
}
