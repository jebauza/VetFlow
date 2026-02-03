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
     * Get a JWT via given credentials.
     *
     * @param LoginRequest $request [explicite description]
     *
     * @return \Illuminate\Http\JsonResponse
     */
    /**
     * @lrd:start
     *
     * **200 OK**
     * ```json
     *{"access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vbG9jYWxob3N0OjgwODAvYXBpL2F1dGgvbG9naW4iLCJpYXQiOjE3NTg4OTk4MDksImV4cCI6MTc1ODkwMzQwOSwibmJmIjoxNzU4ODk5ODA5LCJqdGkiOiJTc2s5VDc5Q2QxOHFxWThRIiwic3ViIjoiOWZmODU5MGUtOGU5My00NWY3LWFmZmQtZTc2NDQ1YzM2MDU1IiwicHJ2IjoiMjNiZDVjODk0OWY2MDBhZGIzOWU3MDFjNDAwODcyZGI3YTU5NzZmNyJ9.ObqGjR67L5bdkwbO9D5Vuth8mfoTiLbomIbWAYwvnSA", "token_type": "bearer", "expires_at": "2025-09-26 16:16:49"}
     * ```
     *
     * **401 Unauthorized**
     * ```json
     *{"message":"Unauthorized"}
     * ```
     *
     * **422 Unprocessable Entity**
     * ```json
     *{"email":["The email field must be a valid email address."],"password":["The password field is required."]}
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
        $credentials = $request->validated();

        try {
            DB::beginTransaction();
            if (!$token = Auth::attempt($credentials)) {
                return $this->sendError('Unauthorized', 401);
            }
            DB::commit();

            return $this->respondWithToken($token);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError500($e->getMessage());
        }
    }

    /**
     * Get the authenticated User.
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
     *{"id":"a00ea363-ee42-446d-8c9b-d9f0bfb96048","email":"alberto.considine@example.org","name":"Orlo Mitchell MD","surname":"Goldner","avatar":null,"permissions":[],"roles":["receptionist"]}
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
    public function me(Request $request): JsonResponse
    {
        return response()->json($this->authService->me());
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
     *{"access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vbG9jYWxob3N0OjgwODAvYXBpL2F1dGgvbG9naW4iLCJpYXQiOjE3NTg4OTk4MDksImV4cCI6MTc1ODkwMzQwOSwibmJmIjoxNzU4ODk5ODA5LCJqdGkiOiJTc2s5VDc5Q2QxOHFxWThRIiwic3ViIjoiOWZmODU5MGUtOGU5My00NWY3LWFmZmQtZTc2NDQ1YzM2MDU1IiwicHJ2IjoiMjNiZDVjODk0OWY2MDBhZGIzOWU3MDFjNDAwODcyZGI3YTU5NzZmNyJ9.ObqGjR67L5bdkwbO9D5Vuth8mfoTiLbomIbWAYwvnSA", "token_type": "bearer", "expires_at": "2025-09-26 16:16:49"}
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
    public function refresh(Request $request): JsonResponse
    {
        return $this->respondWithToken(Auth::refresh());
    }

    /**
     * Log the user out (Invalidate the token).
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
    public function logout(Request $request): JsonResponse
    {
        Auth::logout();

        return response()->json(['message' => 'Successfully logged out']);
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
