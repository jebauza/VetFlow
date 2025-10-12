<?php

namespace App\Modules\Permission\Controllers\Api;

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
     *{"message":"Request processed successfully","data":[{"id":"a0150e1c-6a44-4fda-8945-31ba48d63de5","name":"superadmin"},{"id":"a0150e1c-6d3d-44af-b36b-4468fc4f8541","name":"admin"},{"id":"a0150e1c-6e2f-4ff2-929e-42779e172481","name":"register_role"}]}
     * ```
     *
     * **401 Unauthorized**
     * ```json
     *{"message":"Unauthenticated."}
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
    public function __invoke(PermissionService $service)
    {
        return $this->sendResponse(
            null,
            PermissionResource::collection($service->getAllPermissions())
        );
    }
}
