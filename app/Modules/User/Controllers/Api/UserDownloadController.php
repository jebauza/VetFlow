<?php

namespace App\Modules\User\Controllers\Api;

use Illuminate\Support\Str;
use App\Common\Responses\ApiResponse;
use Illuminate\Support\Facades\Storage;
use App\Common\Controllers\ApiController;
use App\Modules\User\Services\UserService;

class UserDownloadController extends ApiController
{
    public function __construct(
        protected readonly UserService $service
    ) {}

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
     * **404 Not Found**
     * ```json
     *{"message": "Avatar not found"}
     * ```
     *
     * **422 Unprocessable Entity**
     * ```json
     *{"user":["Must be a valid UUID."]}
     * ```
     *
     * **500 Internal Server Error**
     * ```json
     *{"message":"Internal Server Error"}
     * ```
     *
     * @lrd:end
     *
     * @LRDresponses 200|404|422|500
     */
    public function avatar(string $id)
    {
        if (!Str::isUuid($id)) {
            return ApiResponse::validation(['user' => [__('Must be a valid UUID.')]]);
        }

        $user = $this->service->findById($id);

        if (!Storage::disk('public')->exists($user->avatar)) {
            return ApiResponse::error(__('Avatar not found'), 404);
        }

        return Storage::disk('public')->download($user->avatar);
    }
}
