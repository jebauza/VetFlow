<?php

namespace App\Modules\User\Controllers\Api;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Modules\User\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
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
        protected readonly UserService $userService
    ) {}

    /**
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
     *{"message":"Request processed successfully","data":[{"id":"2f78f669-8123-4564-9936-6de15b6dfc73","name":"admin@example.com","surname":"admin@example.com","email":"admin@example.com","roles":[],"all_permissions":[]},{"id":"a046db2d-af61-42c1-a9e9-b1c5a5a65e92","name":"Alexandrine Aufderhar","surname":"Hermiston","email":"yauer@example.org","roles":[{"id":"a046db2c-ad46-4e23-89e6-7edd826d76fc","name":"Admin"}],"all_permissions":[{"id":"a046db2c-80e7-4fcd-a077-dd1b003e85c7","name":"admin"},{"id":"a046db2c-81fa-453a-bb64-bfa7b2e3fb5e","name":"role.register"},{"id":"a046db2c-8315-4962-b088-5175902065c8","name":"role.list"},{"id":"a046db2c-843b-4544-bb63-f1709174d305","name":"role.edit"},{"id":"a046db2c-8538-4469-aeb6-36483b1b4683","name":"role.delete"},{"id":"a046db2c-8626-4e22-9b8c-f7bf70f436bf","name":"veterinary.register"},{"id":"a046db2c-8714-4331-a59e-6da92cf68718","name":"veterinary.list"},{"id":"a046db2c-882a-4973-83fd-f6880c9e158d","name":"veterinary.edit"},{"id":"a046db2c-895b-4ee1-918a-7947f644c55e","name":"veterinary.delete"}]}]}
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
            UserResource::collection($this->userService->getUsers($request->input('search')))
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
     *{"message":"Created successfully","data":{"id":"a043563d-e5eb-4304-8a9d-f5c5411c646d","name":"Francisdailin","surname":"Cobas","email":"francisdailin@gmail.com","permissions":[]}}
     * ```
     *
     * **401 Unauthorized**
     * ```json
     *{"message":"Unauthenticated."}
     * ```
     *
     * **422 Unprocessable Entity**
     * ```json
     *{"email":["The email field is required."],"name":["The name field is required."],"surname":["The surname field is required."],"password":["The password field is required."]}
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
        $createUserDTO = CreateUserDTO::fromRequest($request);

        try {
            DB::beginTransaction();
            $user = $this->userService->createUser($createUserDTO, $request->file(CreateUserDTO::AVATAR));
            DB::commit();

            return $this->sendResponse(
                __('Created successfully'),
                (new UserResource($user)),
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
     * - Retrieve and display a specific user by its ID.
     *
     * **200 OK**
     * ```json
     *{"message":"Request processed successfully","data":{"id":"a04733c5-6e3e-4df9-b0aa-ce14fadeb9ea","name":"Earnestine Sanford","surname":"Schumm","email":"fkohler@example.net","avatar":null,"roles":[{"id":"a04733c4-6c94-4511-ab2c-f303f5b634b9","name":"Assistant"}],"all_permissions":[{"id":"a04733c4-35e3-4091-8001-a9f5fa42cd3f","name":"staff.delete"},{"id":"a04733c4-37c3-4d70-8472-1572766142a7","name":"appointment.register"},{"id":"a04733c4-39d5-4d63-bed4-0f01365cdb60","name":"appointment.list"},{"id":"a04733c4-3c28-46ca-a15a-87caf3e21661","name":"appointment.edit"},{"id":"a04733c4-3eeb-4017-be06-affc32598902","name":"appointment.delete"},{"id":"a04733c4-426e-4813-9720-b4d27810ae3a","name":"payment.show"},{"id":"a04733c4-4575-4364-8983-5f9776c4cf1a","name":"payment.edit"},{"id":"a04733c4-483e-47b4-b7a2-9bbb45b4aa94","name":"calendar"},{"id":"a04733c4-4adc-4657-9c8d-886f9aa679ff","name":"vaccionation.register"}]}}
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
            return $this->sendError422(['user' => [__('Must be a valid UUID.')]]);
        }

        $user = $this->userService->getUserById($id);

        return $this->sendResponse(
            null,
            (new UserResource($user))
        );
    }

    /**
     * @lrd:start
     *
     * **Notes**
     * - Requires **Access Token** obtained from **auth/login**, configuration in **auth/me**.
     *
     * **Description**
     * - Retrieve and display a specific user avatar by its ID.
     *
     * **200 OK**
     *
     * **401 Unauthorized**
     * ```json
     *{"message":"Unauthenticated."}
     * ```
     *
     * **404 Not Found**
     * ```json
     *{"message": "Avatar not found"}
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
    public function avatar(string $id)
    {
        if (!Str::isUuid($id)) {
            return $this->sendError422(['user' => [__('Must be a valid UUID.')]]);
        }

        $user = $this->userService->getUserById($id);

        if (!$user || !$user->avatar) {
            return  $this->sendError(__('Avatar not found'), 404);
        }

        if (!Storage::disk('public')->exists($user->avatar)) {
            return $this->sendError(__('Avatar not found'), 404);
        }

        return Storage::disk('public')->download($user->avatar);
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
     *{"message":"Updated successfully","data":{"id":"a043563d-e5eb-4304-8a9d-f5c5411c646d","name":"Francisdailin","surname":"Cobas","email":"francisdailin@gmail.com","permissions":[]}}
     * ```
     *
     * **401 Unauthorized**
     * ```json
     *{"message":"Unauthenticated."}
     * ```
     *
     * **422 Unprocessable Entity**
     * ```json
     *{"email":["The email field is required."],"name":["The name field is required."],"surname":["The surname field is required."],"password":["The password field is required."]}
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
    public function update(UpdateUserRequest $request, string $id)
    {
        $updateUserDTO = new UpdateUserDTO(...$request->validated());
        $user = $this->userService->getUserById($id);

        try {
            DB::beginTransaction();
            $user = $this->userService->updateUser(
                $user->{User::ID},
                $updateUserDTO,
                $request->file(UpdateUserDTO::AVATAR)
            );
            DB::commit();

            return $this->sendResponse(
                __('Updated successfully'),
                (new UserResource($user)),
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
     *{"message": "No query results for model a014efff-69d0-46a4-877f-6b98c428e978"}
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
            return $this->sendError422(['user' => [__('Must be a valid UUID.')]]);
        }

        $user = $this->userService->getUserById($id);

        try {
            DB::beginTransaction();
            $this->userService->deleteUser($user->{User::ID});
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
