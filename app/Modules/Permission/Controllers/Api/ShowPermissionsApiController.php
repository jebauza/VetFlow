<?php

namespace App\Modules\Permission\Controllers\Api;

use Illuminate\Http\JsonResponse;
use App\Common\Responses\ApiResponse;
use App\Common\Controllers\ApiController;
use App\Modules\Permission\Services\PermissionService;
use App\Modules\Permission\Resources\PermissionResource;

class ShowPermissionsApiController extends ApiController
{
    /**
     * @lrd:start
     *
     * **Notes**
     * - Requires **Access Token** obtained from **auth/login**, configuration in **auth/me**.
     *
     * **Description**
     * - Retrieves a list of all permissions within the system.
     *
     * **200 OK**
     * ```json
     *{"message":"OK","data":[{"id":"a1000f18-4d8c-43e6-abd4-de01a7184dd0","name":"admin"},{"id":"a1000f18-5050-4625-8c87-a306916c446a","name":"role.register"}]}
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
    public function __invoke(PermissionService $service): JsonResponse
    {
        return ApiResponse::successData(
            PermissionResource::collection($service->getPermissions()),
        );
    }
}
