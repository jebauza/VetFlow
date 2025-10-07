<?php

namespace App\Api\Auth\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Api\Auth\Requests\LoginRequest;
use App\Api\Auth\Requests\RegisterRequest;
use App\Models\Permission;

class AuthController extends Controller
{
    /**
     * Register a User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    /**
     * @lrd:start
     *
     * **201 Created**
     * ```json
     *{"id":"9ff8ac68-6f9b-4c14-96a8-c4086f30fabf","email":"pepe@gmail.com","name":"Pepe Gonzalez"}
     * ```
     *
     * **422 Unprocessable Entity**
     * ```json
     *{"name":["The name field is required."],"password":["The password field is required."]}
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
    public function register(RegisterRequest $request)
    {
        try {
            DB::beginTransaction();

            $user = new User;
            $user->{User::NAME} = $request->{User::NAME};
            $user->{User::EMAIL} = $request->{User::EMAIL};
            $user->{User::PASSWORD} = Hash::make($request->{User::PASSWORD});
            $user->save();

            DB::commit();

            return response()->json($user->only(User::ID, User::EMAIL, User::NAME), 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }
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
     *{"message":"Unauthenticated."}
     * ```
     *
     * **422 Unprocessable Entity**
     * ```json
     *{"message":"The email field must be a valid email address.","errors":{"email":["The email field must be a valid email address."]}}
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
    public function login(LoginRequest $request)
    {
        $credentials = $request->only([User::EMAIL, User::PASSWORD]);

        try {
            DB::beginTransaction();

            if (! $token = Auth::attempt($credentials)) {
                return response()->json(['error' => 'Unauthorised'], 401);
            }

            DB::commit();

            return $this->respondWithToken($token);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
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
    public function me(Request $request)
    {
        $user = auth()->user();
        $data = $user->only(
            User::ID,
            User::EMAIL,
            User::NAME,
            User::SURNAME,
            User::AVATAR,
        );
        $data['permissions'] = $user->getAllPermissions()->pluck(Permission::NAME);
        $data['roles'] = $user->getRoleNames();

        return response()->json($data);
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
    public function refresh(Request $request)
    {
        return $this->respondWithToken(auth()->refresh());
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
    public function logout(Request $request)
    {
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        $expires_in_minutes = auth()->factory()->getTTL();

        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_at' => now()->addMinutes($expires_in_minutes)->toDateTimeString(),
        ]);
    }
}
