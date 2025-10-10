<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Auth\Controllers\Api\AuthController;

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
});
