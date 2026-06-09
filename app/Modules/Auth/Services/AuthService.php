<?php

namespace App\Modules\Auth\Services;

use App\Modules\Auth\DTOs\Inputs\LoginInputDTO;
use App\Modules\Auth\DTOs\Outputs\AuthTokenDTO;
use App\Modules\Auth\Exceptions\LoginFailedException;
use App\Modules\User\DTOs\CreateUserDTO;
use App\Modules\User\Models\User;
use App\Modules\User\Repositories\UserRepository;
use App\Modules\User\Services\UserService;
use Illuminate\Support\Facades\Auth;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use PHPOpenSourceSaver\JWTAuth\Token;

class AuthService
{
    public function __construct(
        protected readonly UserService $userService,
        protected readonly UserRepository $userRepo
    ) {}

    public function register(CreateUserDTO $createUserDTO): AuthTokenDTO
    {
        $user = $this->userService->create($createUserDTO);
        $token = JWTAuth::fromUser($user);

        return new AuthTokenDTO($token, $user);
    }

    public function login(LoginInputDTO $dto): AuthTokenDTO
    {
        $credentials = $dto->toArray();

        if (!$token = JWTAuth::attempt($credentials) /* !$token = Auth::attempt($credentials) */) {
            throw new LoginFailedException(__('auth.failed'));
        }

        return new AuthTokenDTO($token, Auth::user());
    }

    public function me(string $userId): User
    {
        return $this->userRepo->findOrFail($userId);
    }

    public function refresh(Token $token): AuthTokenDTO
    {
        $newToken = JWTAuth::refresh($token);
        $user = JWTAuth::setToken($newToken)->toUser();

        return new AuthTokenDTO($newToken, $user);
    }

    public function logout(Token $token): void
    {
        JWTAuth::invalidate($token);
        // Auth::logout();
    }
}
