<?php

namespace App\Modules\Schedule\Controllers;

use App\Modules\Schedule\DTOs\Inputs\CreateScheduleDayInputDTO;
use App\Common\Responses\ApiResponse;
use App\Common\Controllers\ApiController;
use App\Common\Helpers\UuidHelper;
use App\Modules\Schedule\Models\ScheduleDay;
use App\Modules\Schedule\Requests\StoreScheduleRequest;
use App\Modules\Schedule\Resources\ScheduleDayResource;
use App\Modules\Schedule\Services\ScheduleDayService;
use Illuminate\Support\Carbon;

class ScheduleDayApiController extends ApiController
{
    public function __construct(
        protected readonly ScheduleDayService $service
    ) {}

    /**
     * @lrd:start
     *
     * **Notes**
     * - Requires **Access Token** obtained from **auth/login**, configuration in **auth/me**.
     *
     * **Description**
     * - Create a new ScheduleDay with the provided attributes.
     *
     * **201 Created**
     * ```json
     *{"message":"Created","data":{"id":"a201c1c2-2599-480c-bcbe-3f9506d71bff","user_id":"a10c2d12-f693-42db-a622-fea77f115ea4","date":"2026-06-13","hours":[{"id":"507412b6-2b08-4ea9-912d-1a083d5cfc0d","start":"13:30:00","end":"13:45:00","hour":13}]}}
     * ```
     *
     * **400 Bad Request**
     * ```json
     *{"message":"(user_id, date)=(a10c2d12-f89b-4916-9cb0-9a2b0b511727, 2026-06-13) already exists."}
     * ```
     *
     * **401 Unauthorized**
     * ```json
     *{"message":"Unauthorized","errors":{"auth":["Authentication token is invalid or expired"]}}
     * ```
     *
     * **404 Not Found**
     * ```json
     *{"message":"The user_id 'c64fec2f-8f5f-4368-a8f9-6b93af278dde' is not a veterinary."}
     * ```
     *
     * **422 Unprocessable Entity**
     * ```json
     *{"message":"Validation errors","errors":{"date":["The date field must match the format Y-m-d."],"user_id":["The user id field must be a valid UUID."],"schedule_hour_ids":["The schedule hour ids field is required."],"schedule_hour_ids.0":["The schedule_hour_ids.0 field must be a valid UUID."]}}
     * ```
     *
     * **500 Internal Server Error**
     * ```json
     *{"message":"Internal Server Error"}
     * ```
     *
     * @lrd:end
     *
     * @LRDresponses 201|400|401|404|422|500
     */
    public function store(StoreScheduleRequest $request)
    {
        $inputDto = new CreateScheduleDayInputDTO(
            Carbon::create($request->validated('date')),
            $request->validated('user_id'),
            $request->validated('schedule_hour_ids')
        );

        /** @var ScheduleDay $scheduleDay */
        $scheduleDay = $this->service->create($inputDto);

        return ApiResponse::created(
            new ScheduleDayResource($scheduleDay)
        );
    }

    /**
     * @lrd:start
     *
     * **Notes**
     * - Requires **Access Token** obtained from **auth/login**, configuration in **auth/me**.
     *
     * **Description**
     * - Retrieve and display a specific ScheduleDay by its ID.
     *
     * **200 OK**
     * ```json
     *{"message":"OK","data":{"id":"a201c43e-6ccb-42a5-9fe5-6ff606fbe0a5","user_id":"a10c2d12-f89b-4916-9cb0-9a2b0b511727","date":"2026-06-13","hours":[{"id":"403038fe-043b-495d-8a09-7117ed2933be","start":"09:30:00","end":"09:45:00","hour":9}]}}
     * ```
     *
     * **401 Unauthorized**
     * ```json
     *{"message":"Unauthorized","errors":{"auth":["Authentication token is invalid or expired"]}}
     * ```
     *
     * **404 Not Found**
     * ```json
     *{"message":"The requested resource does not exist"}
     * ```
     *
     * **422 Unprocessable Entity**
     * ```json
     *{"message":"Validation errors","errors":{"ScheduleDay":["Must be a valid UUID."]}}
     * ```
     *
     * **500 Internal Server Error**
     * ```json
     *{"message":"Internal Server Error"}
     * ```
     *
     * @lrd:end
     *
     * @LRDresponses 200|401|404|422|500
     */
    public function show(string $id)
    {
        if (!UuidHelper::isUuid($id))
            return ApiResponse::validation(['scheduleDayId' => [__('Must be a valid UUID.')]]);

        return ApiResponse::successData(
            new ScheduleDayResource($this->service->findById($id))
        );
    }

    /**
     * @lrd:start
     *
     * **Notes**
     * - Requires **Access Token** obtained from **auth/login**, configuration in **auth/me**.
     *
     * **Description**
     * - Create or update the ScheduleDay for the given user and date.
     *
     * **201 Created**
     * ```json
     *{"message":"Created","data":{"id":"a201c1c2-2599-480c-bcbe-3f9506d71bff","user_id":"a10c2d12-f693-42db-a622-fea77f115ea4","date":"2026-06-13","hours":[{"id":"507412b6-2b08-4ea9-912d-1a083d5cfc0d","start":"13:30:00","end":"13:45:00","hour":13}]}}
     * ```
     *
     * **400 Bad Request**
     * ```json
     *{"message":"(user_id, date)=(a10c2d12-f89b-4916-9cb0-9a2b0b511727, 2026-06-13) already exists."}
     * ```
     *
     * **401 Unauthorized**
     * ```json
     *{"message":"Unauthorized","errors":{"auth":["Authentication token is invalid or expired"]}}
     * ```
     *
     * **404 Not Found**
     * ```json
     *{"message":"The user_id 'c64fec2f-8f5f-4368-a8f9-6b93af278dde' is not a veterinary."}
     * ```
     *
     * **422 Unprocessable Entity**
     * ```json
     *{"message":"Validation errors","errors":{"date":["The date field must match the format Y-m-d."],"user_id":["The user id field must be a valid UUID."],"schedule_hour_ids":["The schedule hour ids field is required."],"schedule_hour_ids.0":["The schedule_hour_ids.0 field must be a valid UUID."]}}
     * ```
     *
     * **500 Internal Server Error**
     * ```json
     *{"message":"Internal Server Error"}
     * ```
     *
     * @lrd:end
     *
     * @LRDresponses 200|401|404|422|500
     */
    public function upsert(StoreScheduleRequest $request)
    {
        $inputDto = new CreateScheduleDayInputDTO(
            Carbon::create($request->validated('date')),
            $request->validated('user_id'),
            $request->validated('schedule_hour_ids')
        );

        /** @var ScheduleDay $scheduleDay */
        $scheduleDay = $this->service->upsert($inputDto);

        return ApiResponse::successData(
            new ScheduleDayResource($scheduleDay)
        );
    }

    /**
     * @lrd:start
     *
     * **Notes**
     * - Requires **Access Token** obtained from **auth/login**, configuration in **auth/me**.
     *
     * **Description**
     * - Delete the specified ScheduleDay and its associated hours by ID.
     *
     * **200 OK**
     * ```json
     *{"message":"Deleted successfully"}
     * ```
     *
     * **401 Unauthorized**
     * ```json
     *{"message":"Unauthorized","errors":{"auth":["Authentication token is invalid or expired"]}}
     * ```
     *
     * **404 Not Found**
     * ```json
     *{"message":"The requested resource does not exist"}
     * ```
     *
     * **422 Unprocessable Entity**
     * ```json
     *{"message":"Validation errors","errors":{"userId":["Must be a valid UUID."]}}
     * ```
     *
     * **500 Internal Server Error**
     * ```json
     *{"message":"Internal Server Error"}
     * ```
     *
     * @lrd:end
     *
     * @LRDresponses 200|401|404|422|500
     */
    public function destroy(string $id)
    {
        if (!UuidHelper::isUuid($id)) {
            return ApiResponse::validation(['userId' => [__('Must be a valid UUID.')]]);
        }

        $this->service->delete($id);

        return ApiResponse::success(__('Deleted successfully'));
    }
}
