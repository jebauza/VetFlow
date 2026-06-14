<?php

namespace App\Modules\Schedule\Controllers;

use App\Common\Responses\ApiResponse;
use App\Common\Controllers\ApiController;
use App\Modules\Schedule\Resources\ConfigResource;
use App\Modules\Schedule\Services\ScheduleService;

class ScheduleApiController extends ApiController
{
    public function __construct(
        protected readonly ScheduleService $service
    ) {}

    /**
     * @lrd:start
     *
     * **Notes**
     * - Requires **Access Token** obtained from **auth/login**, configuration in **auth/me**.
     *
     * **Description**
     * - Returns the schedule configuration: the list of veterinary roles and all available time slots grouped by hour.
     *
     * **200 OK**
     * ```json
     *{"message":"OK","data":{"roles":[{"id":"a1030860-2a5d-482d-b4d2-8450ea436186","name":"Vet"}],"hours":[{"hour":8,"segments":[{"id":"507412b6-2b08-4ea9-912d-1a083d5cfc0d","start":"08:00:00","end":"08:15:00"},{"id":"607412b6-2b08-4ea9-912d-1a083d5cfc0d","start":"08:15:00","end":"08:30:00"},{"id":"707412b6-2b08-4ea9-912d-1a083d5cfc0d","start":"08:30:00","end":"08:45:00"},{"id":"807412b6-2b08-4ea9-912d-1a083d5cfc0d","start":"08:45:00","end":"09:00:00"}]}]}}
     * ```
     *
     * **401 Unauthorized**
     * ```json
     *{"message":"Unauthorized","errors":{"auth":["Authentication token is invalid or expired"]}}
     * ```
     *
     * **500 Internal Server Error**
     * ```json
     *{"message":"Internal Server Error"}
     * ```
     *
     * @lrd:end
     *
     * @LRDresponses 200|401|500
     */
    public function config()
    {
        $dto = $this->service->config();

        return ApiResponse::successData(
            new ConfigResource($dto)
        );
    }
}
