<?php

namespace App\Modules\User\Controllers\Api;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Modules\User\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Common\Responses\ApiResponse;
use Illuminate\Support\Facades\Storage;
use App\Modules\User\DTOs\CreateUserDTO;
use App\Modules\User\DTOs\UpdateUserDTO;
use App\Common\Controllers\ApiController;
use Illuminate\Support\Facades\Validator;
use App\Modules\User\Services\UserService;
use App\Modules\User\Resources\UserResource;
use App\Modules\User\Requests\StoreUserRequest;
use App\Modules\User\Requests\UpdateUserRequest;

class UserApiController extends ApiController
{
    public function __construct(
        protected readonly UserService $service
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
     * - Retrieves a list of all users within the system.
     *
     *
     * **200 OK**
     * ```json
     *{"message":"OK","data":[{"id":"a1063012-a2ee-4f5e-a0b2-03817335bb12","name":"Test","surname":"Test","email":"test4@test.com","avatar":null,"phone":null,"type_document":null,"n_document":null,"birth_date":null,"designation":null,"gender":null,"roles":[{"id":"a1030860-2a5d-482d-b4d2-8450ea436186","name":"rol test2"}],"all_permissions":[{"id":"a103085f-d38c-4c86-a46a-79b1e5c3c419","name":"veterinary.register"}]}]}
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
     * - Create a new user with the provided attributes.
     *
     * **201 Created**
     * ```json
     *{"message":"Created","data":{"id":"a106317e-889c-40f6-a8dd-cdcffb2b9886","name":"Test8","surname":"Test8","email":"test7@test.com","avatar":"http:\/\/localhost:8080\/storage\/user\/avatars\/P1NohQnOiGctG5beFqp2PIAzuWtNLPBLm1xBZBIq.jpg","phone":"622788616","type_document":"dni","n_document":"jmYYDaHRwh","birth_date":"1989-12-19","designation":"sdsdsdsdsd","gender":"female","roles":[{"id":"a1030860-2a5d-482d-b4d2-8450ea436186","name":"rol test2"}],"all_permissions":[{"id":"a103085f-d38c-4c86-a46a-79b1e5c3c419","name":"veterinary.register"}]}}
     * ```
     *
     * **401 Unauthorized**
     * ```json
     *{"message":"Unauthorized","errors":{"auth":["Authentication token is invalid or expired"]}}
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
     * @LRDresponses 201|401|422|500
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        $dto = CreateUserDTO::fromRequest($request);

        $user = DB::transaction(function () use ($dto) {
            return $this->service->create($dto);
        });

        return ApiResponse::created(new UserResource($user));
    }

    /**
     * @lrd:start
     *
     * **Notes**
     * - Requires **Access Token** obtained from **auth/login**, configuration in **auth/me**.
     *
     * **Description**
     * - Retrieve and display a specific user by its ID.
     *
     * **200 OK**
     * ```json
     *{"message":"OK","data":{"id":"a106317e-889c-40f6-a8dd-cdcffb2b9886","name":"Test8","surname":"Test8","email":"test7@test.com","avatar":"http:\/\/localhost:8080\/storage\/user\/avatars\/P1NohQnOiGctG5beFqp2PIAzuWtNLPBLm1xBZBIq.jpg","phone":"622788616","type_document":"dni","n_document":"jmYYDaHRwh","birth_date":"1989-12-19","designation":"sdsdsdsdsd","gender":"female","roles":[{"id":"a1030860-2a5d-482d-b4d2-8450ea436186","name":"rol test2"}],"all_permissions":[{"id":"a103085f-d38c-4c86-a46a-79b1e5c3c419","name":"veterinary.register"}]}}
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
     *{"message":"Validation errors","errors":{"user":["Must be a valid UUID."]}}
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
            return ApiResponse::validation(['user' => [__('Must be a valid UUID.')]]);
        }

        $user = $this->service->findById($id);

        return ApiResponse::successData(
            new UserResource($user)
        );
    }

    /**
     * @lrd:start
     *
     * **Notes**
     * - Requires **Access Token** obtained from **auth/login**, configuration in **auth/me**.
     *
     * **Description**
     * - Update a user with the provided attributes.
     *
     * **200 OK**
     * ```json
     *{"message":"OK","data":{"id":"a106317e-889c-40f6-a8dd-cdcffb2b9886","name":"Test8","surname":"Test8","email":"test7@test.com","avatar":"http:\/\/localhost:8080\/storage\/user\/avatars\/P1NohQnOiGctG5beFqp2PIAzuWtNLPBLm1xBZBIq.jpg","phone":"622788616","type_document":"dni","n_document":"jmYYDaHRwh","birth_date":"1989-12-19","designation":"sdsdsdsdsd","gender":"female","roles":[{"id":"a1030860-2a5d-482d-b4d2-8450ea436186","name":"rol test2"}],"all_permissions":[{"id":"a103085f-d38c-4c86-a46a-79b1e5c3c419","name":"veterinary.register"}]}}
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
     * @LRDresponses 200|401|404|422|500
     */
    public function update(UpdateUserRequest $request, string $id)
    {
        $dto = new UpdateUserDTO(...$request->validated());

        $user = DB::transaction(function () use ($id, $dto) {
            return $this->service->update($id, $dto);
        });

        return ApiResponse::successData(
            new UserResource($user)
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
     *{"message":"Validation errors","errors":{"user":["Must be a valid UUID."]}}
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
        if (!Str::isUuid($id)) {
            return ApiResponse::validation(['user' => [__('Must be a valid UUID.')]]);
        }

        DB::transaction(function () use ($id) {
            return $this->service->delete($id);
        });

        return ApiResponse::success(__('Deleted successfully'));
    }
}
