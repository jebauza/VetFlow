<?php

namespace App\Modules\Role\Controllers\Api;

use Illuminate\Http\Request;
use App\Modules\Role\Models\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Common\Controllers\ApiController;
use Illuminate\Support\Facades\Validator;
use App\Modules\Role\Services\RoleService;
use App\Modules\Role\Resources\RoleResource;
use App\Modules\Role\Requests\StoreRoleRequest;

class RoleController extends ApiController
{
    protected RoleService $service;

    public function __construct(RoleService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    /**
     * @lrd:start
     *
     * **Notes**
     * - Requires **Access Token** obtained from **auth/login**, configuration in **auth/me**.
     *
     * **Description**
     * - Retrieves a list of all roles within the system.
     *
     * **200 OK**
     * ```json
     *{"message":"Request processed successfully","data":[{"id":"a014ac68-f372-4793-808a-75e50142bbb4","name":"admin"},{"id":"a014ac68-f499-4d6d-b424-f0fe8ab4aa9f","name":"vet"},{"id":"a014ac68-f4fc-4c5e-af83-b163717abc24","name":"assistant"},{"id":"a014ac68-f559-45e0-8c49-130e0c795907","name":"receptionist"}]}
     * ```
     *
     * **500 Internal Server Error**
     * ```json
     *{"message":"Internal Server Error"}
     * ```
     *
     * @lrd:end
     *
     * @LRDresponses 200|500
     */
    public function index(): JsonResponse
    {
        return $this->sendResponse(
            null,
            RoleResource::collection($this->service->getAllRoles())
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRoleRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();
            $role = $this->service->createRole($request->validated());
            DB::commit();

            return $this->sendResponse(
                __('Saved successfully'),
                (new RoleResource($role)),
                201
            );
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->sendError500($th->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $validator = Validator::make(['id' => $id], [
            'id' => 'required|uuid',
        ]);

        if ($validator->fails()) {
            return $this->sendError422($validator->errors()->toArray());
        }

        $role = $this->service->getRoleById($id);

        return $this->sendResponse(
            null,
            (new RoleResource($role))
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        dd('role update');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        dd('role destroy');
    }
}
