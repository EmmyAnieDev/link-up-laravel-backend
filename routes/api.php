<?php

use App\Http\Controllers\v1\AuthController;
use App\Http\Controllers\v1\UserController;
use Illuminate\Support\Facades\Route;


Route::group(['prefix' => 'v1'], function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);

        Route::group(['prefix' => 'users'], function () {
            Route::get('', [UserController::class, 'index']);
            Route::get('/profile', [UserController::class, 'showProfile']);
            Route::put('/update-profile', [UserController::class, 'updateProfile']);
            Route::delete('/delete-account', [UserController::class, 'deleteAccount']);
            Route::post('/upload-photo', [UserController::class, 'uploadProfileImage']);
        });
    });
});

