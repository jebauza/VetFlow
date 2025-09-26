<?php

namespace App\Api\Auth\Controllers;

use Validator;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Api\Auth\Requests\LoginRequest;
use App\Api\Auth\Requests\RegisterRequest;

class AuthController extends Controller
{
    /**
     * Register a User.
     *
     * @return \Illuminate\Http\JsonResponse
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
     * @return \Illuminate\Http\JsonResponse
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
    public function me()
    {
        $data = auth()->user()->only(User::ID, User::EMAIL, User::NAME);
        return response()->json($data);
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
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
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }
}
