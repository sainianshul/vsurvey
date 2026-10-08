<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('admin.dashboard');
});

Route::prefix('admin')->group(function () {
    // Auth Routes (Named login instead of admin.login)
    Route::get('login', [\App\Http\Controllers\Admin\AuthController::class, 'showLogin'])->name('login');
    Route::post('login', [\App\Http\Controllers\Admin\AuthController::class, 'login'])->name('login.post');
    Route::post('logout', [\App\Http\Controllers\Admin\AuthController::class, 'logout'])->name('logout');

    Route::name('admin.')->group(function () {

    Route::middleware(['auth'])->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard/stats', [\App\Http\Controllers\Admin\DashboardController::class, 'stats'])->name('dashboard.stats');

        // Voice to Text (Survey)
        Route::get('/voice-to-text', [\App\Http\Controllers\Admin\DashboardController::class, 'voiceToText'])->name('voice-to-text');
        Route::post('/voice-to-text', [\App\Http\Controllers\Admin\DashboardController::class, 'processVoiceToText'])->name('voice-to-text.process');
        
        // AI Logs & Stats (Old)
        Route::get('/ai-logs', [\App\Http\Controllers\Admin\DashboardController::class, 'aiLogs'])->name('ai-logs');

        // New Call Logs (Manual Upload & Datatable)
        Route::resource('call-logs', \App\Http\Controllers\Admin\CallLogController::class)->only(['index', 'create', 'store', 'show']);

        // Profile
        Route::get('profile', [\App\Http\Controllers\Admin\ProfileController::class, 'index'])->name('profile.index');
        Route::post('profile', [\App\Http\Controllers\Admin\ProfileController::class, 'update'])->name('profile.update');


        // Users (Buyers/Sellers) CRUD
        Route::prefix('users')->name('users.')->group(function () {
            Route::get('data', [\App\Http\Controllers\Admin\UserController::class, 'data'])->name('data');
            
            Route::get('search', [\App\Http\Controllers\Admin\UserController::class, 'search'])->name('search');
            Route::get('blocked', [\App\Http\Controllers\Admin\UserController::class, 'blocked'])->name('blocked');
            Route::get('blocked/data', [\App\Http\Controllers\Admin\UserController::class, 'blockedData'])->name('blocked.data');
            Route::post('{user}/unblock', [\App\Http\Controllers\Admin\UserController::class, 'unblock'])->name('unblock');
            
            Route::get('deleted', [\App\Http\Controllers\Admin\UserController::class, 'deleted'])->name('deleted');
            Route::get('deleted/data', [\App\Http\Controllers\Admin\UserController::class, 'deletedData'])->name('deleted.data');
            Route::post('{user}/restore', [\App\Http\Controllers\Admin\UserController::class, 'restore'])->name('restore');
            
            Route::post('{user}/status', [\App\Http\Controllers\Admin\UserController::class, 'updateStatus'])->name('update-status');
            Route::post('{user}/revoke-token', [\App\Http\Controllers\Admin\UserController::class, 'revokeToken'])->name('revoke-token');
        });
        Route::resource('users', \App\Http\Controllers\Admin\UserController::class);
    });
    });
});