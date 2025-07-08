<?php

use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminMessageController;


Route::post('/signup', [UserController::class, 'signup'])
    ->middleware('throttle:5,1');

Route::post('/login', [UserController::class, 'login'])

    ->middleware('throttle:5,1');

Route::post('/GoogleAuth', [UserController::class, 'googleLogin'])

    ->middleware('throttle:5,1');

Route::post('/send-otp', [UserController::class, 'sendOtp'])
    ->middleware('throttle:3,1');

Route::post('/reset-password', [UserController::class, 'resetPassword'])
    ->middleware('throttle:3,1');

Route::middleware('auth.api:sanctum')->group(function () {
    //
    Route::patch('/update-password', [UserController::class, 'updatePassword'])
        ->middleware('throttle:10,1');

    Route::post('/logout', [UserController::class, 'logout']);

    // 
    Route::prefix('me')->group(function () {
        Route::get('/', [UserController::class, 'getMe']);
        Route::put('/', [UserController::class, 'updateMe']);
    });
});


//  
Route::prefix('users')->group(function () {
    Route::get('/{id}', [UserController::class, 'getUserById']);
    Route::delete('/{id}', [UserController::class, 'deleteUser']);
});

Route::post('/admin/send-message', [AdminMessageController::class, 'sendMessage']);
