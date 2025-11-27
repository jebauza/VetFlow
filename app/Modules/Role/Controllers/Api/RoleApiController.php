<?php

namespace App\Modules\Role\Controllers\Api;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Modules\Role\Models\Role;
use Illuminate\Http\JsonResponse;
use App\Modules\Role\DTOs\RoleDTO;
use Illuminate\Support\Facades\DB;
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
     *{"message":"Request processed successfully","data":[{"id":"a014ac68-f372-4793-808a-75e50142bbb4","name":"admin"},{"id":"a014ac68-f499-4d6d-b424-f0fe8ab4aa9f","name":"vet"},{"id":"a014ac68-f4fc-4c5e-af83-b163717abc24","name":"assistant"},{"id":"a014ac68-f559-45e0-8c49-130e0c795907","name":"receptionist"}]}
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
     * @LRDparam search string
     *
     * @LRDresponses 200|401|500
     */
    public function index(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'search' => 'nullable|string',
        ]);

        if ($validator->fails())
            return $this->sendError422($validator->errors()->toArray());

        return $this->sendResponse(
            null,
            RoleResource::collection($this->service->getRoles($request->input('search')))
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
     *{"message":"Created successfully","data":{"id":"a03998e1-31ea-47d8-baf9-4832176a18d1","name":"Vet 2","date":"2025-10-28 22:59:44","permissions":[{"id":"a0398a9a-7008-4e4f-bf93-cee4ef193e0e","name":"pet.profile"},{"id":"a0398a9a-7161-4b5c-b32f-4c4382a2e30e","name":"staff.register"}]}}
     * ```
     *
     * **401 Unauthorized**
     * ```json
     *{"message":"Unauthenticated."}
     * ```
     *
     * **422 Unprocessable Entity**
     * ```json
     *{"name":["The name field is required."],"permission_ids":["The permission_ids field must be present."],"permission_ids.0":["The permission (a014effe-392c-42ec-a8c9-04ab3c7a43ca) is not valid."]}
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
        $roleDTO = new RoleDTO(...$request->validated());

        try {
            DB::beginTransaction();
            $role = $this->service->createRole($roleDTO);
            DB::commit();

            return $this->sendResponse(
                __('Created successfully'),
                (new RoleResource($role)),
                201
            );
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->sendError500($th->getMessage());
        }
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
     *{"message":"Unauthenticated."}
     * ```
     *
     * **404 Not Found**
     * ```json
     *{"message": "No query results for model [App\\Modules\\Role\\Models\\Role] a014efff-69d0-46a4-877f-6b98c428e978"}
     * ```
     *
     * **422 Unprocessable Entity**
     * ```json
     *{"role":["Must be a valid UUID."]}
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
            return $this->sendError422(['role' => [__('Must be a valid UUID.')]]);
        }

        $role = $this->service->getRoleById($id);

        return $this->sendResponse(
            null,
            (new RoleResource($role))
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
     *{"message":"Updated successfully","data":{"id":"a0398a9a-8d60-477c-92c1-e8b0028529ab","name":"rol test2","date":"2025-10-28 22:19:49","permissions":[{"id":"a0398a9a-6a37-4c20-bf1f-060fc3a4c674","name":"veterinary.profile"},{"id":"a0398a9a-6b35-4c5d-af8b-d2c7df64ee8a","name":"pet.register"}]}}
     * ```
     *
     * **401 Unauthorized**
     * ```json
     *{"message":"Unauthenticated."}
     * ```
     *
     * **404 Not Found**
     * ```json
     *{"message": "No query results for model [App\\Modules\\Role\\Models\\Role] a014efff-69d0-46a4-877f-6b98c428e978"}
     * ```
     *
     * **422 Unprocessable Entity**
     * ```json
     *{"role":["Must be a valid UUID."],"name":["The name field is required."],"permission_ids":["The permission ids field must be present."]}
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
        $roleDTO = new RoleDTO(...$request->validated());
        $role = $this->service->getRoleById($id);

        try {
            DB::beginTransaction();
            $role = $this->service->updateRole($role->{Role::ID}, $roleDTO);
            DB::commit();

            return $this->sendResponse(
                __('Updated successfully'),
                (new RoleResource($role)),
                200
            );
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->sendError500($th->getMessage());
        }
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
     *{"message":"Unauthenticated."}
     * ```
     *
     * **404 Not Found**
     * ```json
     *{"message": "No query results for model [App\\Modules\\Role\\Models\\Role] a014efff-69d0-46a4-877f-6b98c428e978"}
     * ```
     *
     * **422 Unprocessable Entity**
     * ```json
     *{"role":["Must be a valid UUID."]}
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
            return $this->sendError422(['role' => [__('Must be a valid UUID.')]]);
        }

        $role = $this->service->getRoleById($id);

        try {
            DB::beginTransaction();
            $this->service->deleteRole($role->{Role::ID});
            DB::commit();

            return $this->sendResponse(
                __('Deleted successfully')
            );
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->sendError500($th->getMessage());
        }
    }
}
