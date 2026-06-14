<?php

namespace App\Modules\User\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Common\Responses\ApiResponse;
use App\Common\Controllers\ApiController;
use App\Modules\User\DTOs\CreateUserDTO;
use App\Modules\User\Requests\StoreUserRequest;
use Illuminate\Support\Facades\Validator;
use App\Modules\User\Resources\UserResource;
use App\Modules\User\Services\VeterinaryService;

class VeterinaryApiController extends ApiController
{
    public function __construct(
        protected readonly VeterinaryService $service
    ) {}

    /**
     * @LRDparam search string
     *
     * @lrd:start
     *
     * **Notes**
     * - Requires **Access Token** obtained from **auth/login**, configuration in **auth/me**.
     *
     * **Description**
     * - Retrieves a list of all veterinarians (users with at least one permission starting with `veterinary`).
     *
     * **200 OK**
     * ```json
     *{"message":"OK","data":[{"id":"a1063012-a2ee-4f5e-a0b2-03817335bb12","name":"Jane","surname":"Doe","email":"jane@clinic.com","avatar":null,"phone":"622788616","type_document":"dni","n_document":"12345678A","birth_date":"1990-05-15","designation":"Veterinarian","gender":"female","roles":[{"id":"a1030860-2a5d-482d-b4d2-8450ea436186","name":"Vet"}],"all_permissions":[{"id":"a103085f-d38c-4c86-a46a-79b1e5c3c419","name":"veterinary.register"}]}]}
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
            UserResource::collection(
                $this->service->all($request->input('search'))
            )
        );
    }

    /**
     * @lrd:start
     *
     * **Notes**
     * - Requires **Access Token** obtained from **auth/login**, configuration in **auth/me**.
     *
     * **Description**
     * - Create a new veterinarian. The `role_id` must belong to a role that has at least one permission starting with `veterinary`.
     *
     * **201 Created**
     * ```json
     *{"message":"Created","data":{"id":"a106317e-889c-40f6-a8dd-cdcffb2b9886","name":"Jane","surname":"Doe","email":"jane@clinic.com","avatar":null,"phone":"622788616","type_document":"dni","n_document":"12345678A","birth_date":"1990-05-15","designation":"Veterinarian","gender":"female","roles":[{"id":"a1030860-2a5d-482d-b4d2-8450ea436186","name":"Vet"}],"all_permissions":[{"id":"a103085f-d38c-4c86-a46a-79b1e5c3c419","name":"veterinary.register"}]}}
     * ```
     *
     * **401 Unauthorized**
     * ```json
     *{"message":"Unauthorized","errors":{"auth":["Authentication token is invalid or expired"]}}
     * ```
     *
     * **404 Not Found**
     * ```json
     *{"message":"The role_id 'a1030860-2a5d-482d-b4d2-8450ea436186' is not a veterinary role."}
     * ```
     *
     * **422 Unprocessable Entity**
     * ```json
     *{"message":"Validation errors","errors":{"email":["The email field is required."],"name":["The name field is required."],"surname":["The surname field is required."],"password":["The password field is required."]}}
     * ```
     *
     * **500 Internal Server Error**
     * ```json
     *{"message":"Internal Server Error"}
     * ```
     *
     * @lrd:end
     *
     * @LRDresponses 201|401|404|422|500
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        $dto = CreateUserDTO::fromRequest($request);
        $veterinary = $this->service->create($dto);

        return ApiResponse::created(new UserResource($veterinary));
    }
}
