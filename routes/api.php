<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Auth\Controllers\Api\AuthController;
use App\Modules\Role\Controllers\Api\RoleApiController;
use App\Modules\User\Controllers\Api\UserApiController;
use App\Modules\User\Controllers\Api\UserDownloadController;
use App\Modules\User\Controllers\Api\UserPaginateApiController;
use App\Modules\Permission\Controllers\Api\ShowPermissionsApiController;

Route::middleware('api')->group(function () {

    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register'])->name('auth.register');
        Route::post('/login', [AuthController::class, 'login'])->name('auth.login');

        Route::middleware('auth:api')->group(function () {
            Route::get('/me', [AuthController::class, 'me'])->name('auth.me');
            Route::get('/refresh', [AuthController::class, 'refresh'])->name('auth.refresh');
            Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');
        });
    });

    Route::middleware('auth:api')->group(function () {

        // Permissions routes
        Route::get('/permissions', ShowPermissionsApiController::class)->name('permissions.index');

        // Roles routes
        Route::apiResource('roles', RoleApiController::class);

        // Users routes
        Route::name('')->group(function () {
            Route::get('/users/paginate', [UserPaginateApiController::class, 'paginate'])->name('users.paginate');
            Route::get('/users/offset-paginate', [UserPaginateApiController::class, 'offsetPaginate'])->name('users.offset-paginate');
            Route::get('/users/cursor-paginate', [UserPaginateApiController::class, 'cursorPaginate'])->name('users.cursor-paginate');
            Route::get('/users/{user}/download/avatar', [UserDownloadController::class, 'avatar'])
                ->withoutMiddleware(['auth:api'])->name('users.download.avatar');

            Route::apiResource('users', UserApiController::class);
        });
    });
});
