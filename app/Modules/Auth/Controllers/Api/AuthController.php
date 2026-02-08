<?php

namespace App\Modules\Auth\Controllers\Api;

use Illuminate\Http\Request;
use App\Modules\User\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Common\Responses\ApiResponse;
use App\Modules\Auth\DTOs\AuthTokenDTO;
use App\Modules\User\DTOs\CreateUserDTO;
use App\Common\Controllers\ApiController;
use App\Modules\Auth\Services\AuthService;
use App\Modules\Auth\Requests\LoginRequest;
use App\Modules\Auth\Requests\RegisterRequest;
use App\Modules\User\Resources\UserResource;

class AuthController extends ApiController
{
    public function __construct(
        protected readonly AuthService $authService
    ) {}

    /**
     * Register a new user.
     *
     * @param RegisterRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    /**
     * @lrd:start
     *
     * **201 Created**
     * ```json
     *{"message":"User registered successfully","data":{"access_token":"eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vbG9jYWxob3N0OjgwODAvYXBpL2F1dGgvcmVnaXN0ZXIiLCJpYXQiOjE3NzAxNTIxOTcsImV4cCI6MTc3MDIzODU5NywibmJmIjoxNzcwMTUyMTk3LCJqdGkiOiIxa1Y3Z3R0UTlLMUZmQ3lyIiwic3ViIjoiYTBmZTkxNGItMWJkNy00YWE0LWFiODMtM2UwOGJmMWEzNzQyIiwicHJ2IjoiNGE2ZTI1MmQ0OWNjMzVmOWE2ZDI4OTdmZGU0ZjkzMTQ2ZTdjODAyYyJ9.0q1gZ9L1swsPhEeA5RbbQJT6TQ4toS-Egnt9ogeTWS8","token_type":"bearer","expires_in":86400,"expires_at":"2026-02-04 20:56:37","user":{"id":"a0fe914b-1bd7-4aa4-ab83-3e08bf1a3742","name":"jorge Ernesto","email":"jebauza1989@gmail.com"}}}
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
     * @LRDresponses 201|422|500
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $dto = CreateUserDTO::fromRequest($request);

        DB::beginTransaction();
        $authDto = $this->authService->register($dto);
        DB::commit();

        return ApiResponse::created(
            $this->buildTokenResponse($authDto),
            __('User registered successfully')
        );
    }

    /**
     * Login a user and return a token.
     *
     * @param LoginRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    /**
     *
     * @lrd:start
     *
     * **200 OK**
     * ```json
     *{"message":"Login successful","data":{"access_token":"eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vbG9jYWxob3N0OjgwODAvYXBpL2F1dGgvbG9naW4iLCJpYXQiOjE3NzA1NzY5MTgsImV4cCI6MTc3MDY2MzMxOCwibmJmIjoxNzcwNTc2OTE4LCJqdGkiOiI5WDhndktzdkNta0h2cmtWIiwic3ViIjoiNWI0MjQ4YTAtYTA5Ni00YTA4LTg2YWQtZGRjYjk2MjM4NTk4IiwicHJ2IjoiNGE2ZTI1MmQ0OWNjMzVmOWE2ZDI4OTdmZGU0ZjkzMTQ2ZTdjODAyYyJ9.7Fkz9yAD7wU__LHqlk0ggNhDqMtkmgBj00rnVbZ49FU","token_type":"bearer","expires_in":86400,"expires_at":"2026-02-09 18:55:18","user":{"id":"5b4248a0-a096-4a08-86ad-ddcb96238598","name":"superadmin@example.com","email":"superadmin@example.com"}}}
     * ```
     *
     * **401 Unauthorized**
     * ```json
     *{"message":"Unauthorized","errors":{"credentials":["These credentials do not match our records."]}}
     * ```
     *
     * **422 Unprocessable Entity**
     * ```json
     *{"message":"Validation errors","errors":{"email":["The email field is required."],"password":["The password field is required."]}}
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
    public function login(LoginRequest $request): JsonResponse
    {
        $authDTO = $this->authService->login($request->validated());

        return ApiResponse::success(
            __('Login successful'),
            $this->buildTokenResponse($authDTO)
        );
    }

    /**
     * Get the authenticated user's information.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    /**
     *
     * @lrd:start
     *
     * **Set Global Headers**
     * ```json
     *{"Authorization": "Bearer <access_token>", "Content-Type": "application/json", "Accept": "application/json"}
     * ```
     *
     * **200 OK**
     * ```json
     *{"message":"OK","data":{"id":"5b4248a0-a096-4a08-86ad-ddcb96238598","name":"superadmin@example.com","surname":"superadmin@example.com","email":"superadmin@example.com","avatar":null,"phone":null,"type_document":null,"n_document":null,"birth_date":null,"designation":null,"gender":null,"roles":[],"all_permissions":[]}}
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
    public function me(): JsonResponse
    {
        $user = $this->authService->me();

        return ApiResponse::successData(
            new UserResource($user)
        );
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    /**
     *
     * @lrd:start
     *
     * **Set Global Headers**
     * ```json
     *{"Authorization": "Bearer <access_token>", "Content-Type": "application/json", "Accept": "application/json"}
     * ```
     *
     * **200 OK**
     * ```json
     *{"message":"OK","data":{"access_token":"eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vbG9jYWxob3N0OjgwODAvYXBpL2F1dGgvcmVmcmVzaCIsImlhdCI6MTc2OTM4MTM4NSwiZXhwIjoxNzY5Mzg1ODA2LCJuYmYiOjE3NjkzODIyMDYsImp0aSI6Ik1NMFpsT09MUTFLZU9GeEciLCJzdWIiOiIwMTliZjc1OC0xYTM4LTczODEtYWUxYi0xYjg0ZmVmNjdmYTciLCJwcnYiOiI0YTZlMjUyZDQ5Y2MzNWY5YTZkMjg5N2ZkZTRmOTMxNDZlN2M4MDJjIn0.RX0EcotXN0A_ze3UWCPBqPdg8genF2WPbUDa6kb_H6w","token_type":"bearer","expires_in":3600,"expires_at":"2026-01-26 00:03:26"}}
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
    public function refresh(): JsonResponse
    {
        $authDTO = $this->authService->refresh();

        return ApiResponse::successData(
            $this->buildTokenResponse($authDTO)
        );
    }

    /**
     * Logout the authenticated user.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    /**
     *
     * @lrd:start
     *
     * **Set Global Headers**
     * ```json
     *{"Authorization": "Bearer <access_token>", "Content-Type": "application/json", "Accept": "application/json"}
     * ```
     *
     * **200 OK**
     * ```json
     *{"message":"Successfully logged out"}
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
    public function logout(): JsonResponse
    {
        $this->authService->logout();

        return ApiResponse::success(
            __('Successfully logged out')
        );
    }

    protected function buildTokenResponse(AuthTokenDTO $dto): array
    {
        $ttl = Auth::factory()->getTTL(); // auth('api')->factory()->getTTL()

        return [
            'access_token' => $dto->token,
            'token_type' => 'bearer',
            'expires_in' => $ttl * 60,
            'expires_at' => now()->addMinutes($ttl)->toDateTimeString(),
            'user' => $dto->user->only(User::ID, User::NAME, User::EMAIL),
        ];
    }
}
