<?php

use App\Http\Controllers\v1\AuthController;
use App\Http\Controllers\v1\ChatController;
use App\Http\Controllers\v1\ProfileImageController;
use App\Http\Controllers\v1\UserController;
use Illuminate\Support\Facades\Route;


Route::group(['prefix' => 'v1'], function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware(['cors', 'auth:sanctum'])->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);

        Route::group(['prefix' => 'users'], function () {
            Route::get('', [UserController::class, 'index']);
            Route::get('/profile', [UserController::class, 'showProfile']);
            Route::put('/update-profile', [UserController::class, 'updateProfile']);
            Route::delete('/delete-account', [UserController::class, 'deleteAccount']);

            Route::get('/get-photo', [ProfileImageController::class, 'getProfileImage']);
            Route::post('/upload-photo', [ProfileImageController::class, 'uploadProfileImage']);

            Route::get('/fetch-messages', [ChatController::class, 'fetchMessages']);
            Route::post('/send-message', [ChatController::class, 'sendMessage']);

            Route::get('/unread-messages-count', [ChatController::class, 'unreadMessageCounts']);
            Route::post('/remove-unread-messages-count', [ChatController::class, 'removeUnreadMessageCounts']);

        });
    });
});

