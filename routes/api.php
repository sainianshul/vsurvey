<?php

use Illuminate\Support\Facades\Route;

Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'message' => 'API is running',
    ]);
});

Route::prefix('v1')->group(function () {
    
    // Broadcast Auth Route for Sanctum API
    \Illuminate\Support\Facades\Broadcast::routes(['middleware' => ['auth:sanctum']]);

    Route::post('test-broadcasting-auth', function (\Illuminate\Http\Request $request) {
        return \Illuminate\Support\Facades\Broadcast::auth($request);
    })->middleware('auth:sanctum');

    // Public Routes
    Route::prefix('auth')->group(function () {
        Route::post('send-otp', [\App\Http\Controllers\Api\V1\AuthController::class, 'sendOtp']);
        Route::post('verify-otp', [\App\Http\Controllers\Api\V1\AuthController::class, 'verifyOtp']);
        
        Route::middleware('auth:sanctum')->get('me', function (\Illuminate\Http\Request $request) {
            return response()->json(['id' => $request->user()->id]);
        });
    });

    Route::middleware('auth:sanctum')->group(function () {
        // Dashboard
        Route::prefix('dashboard')->group(function () {
            Route::get('/seller', [\App\Http\Controllers\Api\V1\DashboardController::class, 'sellerDashboard']);
        });

        Route::prefix('auth')->group(function () {
            Route::post('logout', [\App\Http\Controllers\Api\V1\AuthController::class, 'logout']);
            Route::post('logout-all', [\App\Http\Controllers\Api\V1\AuthController::class, 'logoutAll']);
            Route::get('me', [\App\Http\Controllers\Api\V1\AuthController::class, 'me']);
        });

        Route::post('profile', [\App\Http\Controllers\Api\V1\ProfileController::class, 'update']);
    });
});