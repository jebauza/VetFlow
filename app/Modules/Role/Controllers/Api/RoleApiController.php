<?php

namespace App\Modules\Role\Controllers\Api;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Modules\Role\DTOs\RoleDTO;
use Illuminate\Support\Facades\DB;
use App\Common\Responses\ApiResponse;
use App\Common\Controllers\ApiController;
use Illuminate\Support\Facades\Validator;
use App\Modules\Role\Services\RoleService;
use App\Modules\Role\Resources\RoleResource;
use App\Modules\Role\Requests\StoreRoleRequest;
use App\Modules\Role\Requests\UpdateRoleRequest;

class RoleApiController extends ApiController
{
    public function __construct(
        protected readonly RoleService $service
    ) {}

    /**
     * @LRDparam search nullable|string
     *
     * @lrd:start
     *
     * **Notes**
     * - Requires **Access Token** obtained from **auth/login**, configuration in **auth/me**.
     *
     * **Description**
     * - Retrieves a list of all roles within the system.
     *
     *
     * **200 OK**
     * ```json
     *{"message":"OK","data":[{"id":"a1000f18-aca4-4a7a-a6c7-d811fb04ec52","name":"Admin","date":"2026-02-04 14:44:13","permissions":[{"id":"a1000f18-4d8c-43e6-abd4-de01a7184dd0","name":"admin"},{"id":"a1000f18-5050-4625-8c87-a306916c446a","name":"role.register"},{"id":"a1000f18-528e-4608-855c-76a7445274b2","name":"role.list"},{"id":"a1000f18-53e8-4fc9-8688-7f9c3ea65f77","name":"role.edit"},{"id":"a1000f18-5535-45e1-a92f-92d7a4615093","name":"role.delete"},{"id":"a1000f18-568e-49ec-8ab9-be240588a659","name":"veterinary.register"},{"id":"a1000f18-5804-45f2-b520-a0d4f79972eb","name":"veterinary.list"},{"id":"a1000f18-595c-4d56-bb59-5dc98603e711","name":"veterinary.edit"},{"id":"a1000f18-5ae7-4331-b5be-146f350c183c","name":"veterinary.delete"}]},{"id":"a1000f18-b32d-442f-91b4-3f4d764e4571","name":"Assistant","date":"2026-02-04 14:44:13","permissions":[{"id":"a1000f18-7613-42cd-9a2c-7211601eef8d","name":"staff.delete"},{"id":"a1000f18-7ad5-451b-b9ba-920eb6fe705d","name":"appointment.register"},{"id":"a1000f18-7f5d-43c5-8d8b-7712629209e5","name":"appointment.list"},{"id":"a1000f18-8714-424f-af4b-f45847ff83d6","name":"appointment.edit"},{"id":"a1000f18-8bc1-4293-8a8a-d4f4e0cbb563","name":"appointment.delete"},{"id":"a1000f18-8ee4-42ff-9e58-7a153d52b478","name":"payment.show"},{"id":"a1000f18-916a-4bea-9cc8-80397de356f3","name":"payment.edit"},{"id":"a1000f18-93c9-4482-b7c0-3346ad727d07","name":"calendar"},{"id":"a1000f18-96d5-4c87-9701-cc8058569c03","name":"vaccionation.register"}]}]}
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
    public function index(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'search' => 'nullable|string',
        ]);

        if ($validator->fails())
            return ApiResponse::validation($validator->errors()->toArray());

        return ApiResponse::successData(
            RoleResource::collection($this->service->all($request->input('search')))
        );
    }

    /**
     * @lrd:start
     *
     * **Notes**
     * - Requires **Access Token** obtained from **auth/login**, configuration in **auth/me**.
     *
     * **Description**
     * - Create a new role with the provided attributes.
     *
     * **201 Created**
     * ```json
     *{"message":"Created","data":{"id":"a10043b5-d5cb-45e4-9025-cb513986d951","name":"Vet 2","date":"2026-02-04 17:11:20","permissions":[{"id":"a1000f18-5804-45f2-b520-a0d4f79972eb","name":"veterinary.list"},{"id":"a1000f18-595c-4d56-bb59-5dc98603e711","name":"veterinary.edit"}]}}
     * ```
     *
     * **401 Unauthorized**
     * ```json
     *{"message":"Unauthorized","errors":{"auth":["Authentication token is invalid or expired"]}}
     * ```
     *
     * **422 Unprocessable Entity**
     * ```json
     *{"message":"Validation errors","errors":{"name":["The name field is required."],"permission_ids":["The permission ids field must be present."],"permission_ids.0":["The permission_ids.0 field must be a valid UUID."]}}
     * ```
     *
     * **500 Internal Server Error**
     * ```json
     *{"message":"Internal Server Error"}
     * ```
     *
     * @lrd:end
     *
     * @LRDresponses 200|401|422|500
     */
    public function store(StoreRoleRequest $request): JsonResponse
    {
        $dto = new RoleDTO(...$request->validated());

        $role = DB::transaction(function () use ($dto) {
            return $this->service->create($dto);
        });

        return ApiResponse::created(new RoleResource($role));
    }

    /**
     * @lrd:start
     *
     * **Notes**
     * - Requires **Access Token** obtained from **auth/login**, configuration in **auth/me**.
     *
     * **Description**
     * - Retrieve and display a specific role by its ID.
     *
     * **200 OK**
     * ```json
     *{"message":"Request processed successfully","data":[{"id":"a014ac68-f372-4793-808a-75e50142bbb4","name":"admin"},{"id":"a014ac68-f499-4d6d-b424-f0fe8ab4aa9f","name":"vet"},{"id":"a014ac68-f4fc-4c5e-af83-b163717abc24","name":"assistant"},{"id":"a014ac68-f559-45e0-8c49-130e0c795907","name":"receptionist"}]}
     * ```
     *
     * **401 Unauthorized**
     * ```json
     *{"message":"Unauthorized","errors":{"auth":["Authentication token is invalid or expired"]}}
     * ```
     *
     * **404 Not Found**
     * ```json
     *{"message":"Not Found","errors":{"resource":["The requested resource does not exist"]}}
     * ```
     *
     * **422 Unprocessable Entity**
     * ```json
     *{"message":"Validation errors","errors":{"role":["Must be a valid UUID."]}}
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
    public function show(string $id): JsonResponse
    {
        if (!Str::isUuid($id)) {
            return ApiResponse::validation(['role' => [__('Must be a valid UUID.')]]);
        }

        $role = $this->service->findById($id);

        return ApiResponse::successData(
            new RoleResource($role)
        );
    }

    /**
     * @lrd:start
     *
     * **Notes**
     * - Requires **Access Token** obtained from **auth/login**, configuration in **auth/me**.
     *
     * **Description**
     * - Update the specified resource in storage.
     *
     * **200 OK**
     * ```json
     *{"message":"OK","data":{"id":"a10043b5-d5cb-45e4-9025-cb513986d951","name":"rol test2","date":"2026-02-04 17:11:20","permissions":[{"id":"a1000f18-5804-45f2-b520-a0d4f79972eb","name":"veterinary.list"}]}}
     * ```
     *
     * **401 Unauthorized**
     * ```json
     *{"message":"Unauthorized","errors":{"auth":["Authentication token is invalid or expired"]}}
     * ```
     *
     * **404 Not Found**
     * ```json
     *{"message":"Not Found","errors":{"resource":["The requested resource does not exist"]}}
     * ```
     *
     * **422 Unprocessable Entity**
     * ```json
     *{"message":"Validation errors","errors":{"name":["The name field is required."],"permission_ids":["The permission ids field must be present."],"permission_ids.0":["The permission_ids.0 field must be a valid UUID."]}}
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
    public function update(UpdateRoleRequest $request, string $id)
    {
        $dto = new RoleDTO(...$request->validated());

        $role = DB::transaction(function () use ($id, $dto) {
            return $this->service->update($id, $dto);
        });

        return ApiResponse::successData(
            new RoleResource($role)
        );
    }

    /**
     * @lrd:start
     *
     * **Notes**
     * - Requires **Access Token** obtained from **auth/login**, configuration in **auth/me**.
     *
     * **Description**
     * - Remove the specified resource from storage.
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
     *{"message":"Not Found","errors":{"resource":["The requested resource does not exist"]}}
     * ```
     *
     * **422 Unprocessable Entity**
     * ```json
     *{"message":"Validation errors","errors":{"role":["Must be a valid UUID."]}}
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
    public function destroy(string $id): JsonResponse
    {
        if (!Str::isUuid($id)) {
            return ApiResponse::validation(['role' => [__('Must be a valid UUID.')]]);
        }

        DB::transaction(function () use ($id) {
            return $this->service->delete($id);
        });

        return ApiResponse::success(__('Deleted successfully'));
    }
}
