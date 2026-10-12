<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Core\Http\Controllers\Api\V1\AuthController;
use Modules\Core\Http\Controllers\Api\V1\MediaController;
use Modules\Core\Http\Controllers\Api\V1\NotificationController;
use Modules\Core\Http\Controllers\Api\V1\NotificationPreferenceController;
use Modules\Core\Http\Controllers\Api\V1\OrganizationController;
use Modules\Core\Http\Controllers\Api\V1\PaymentWebhookController;
use Modules\Core\Http\Controllers\Api\V1\PublicSettingController;
use Modules\Core\Http\Controllers\Api\V1\SearchController;
use Modules\Core\Http\Controllers\Api\V1\PushSubscriptionController;
use Modules\Core\Http\Controllers\Api\V1\UserController;

/*
|--------------------------------------------------------------------------
| Core API Routes
|--------------------------------------------------------------------------
*/

// Authentification (publiques)
Route::prefix('api/v1/auth')->name('auth.')->group(function () {
    Route::post('register', [AuthController::class, 'register'])->name('register');
    Route::post('login', [AuthController::class, 'login'])->name('login');
    Route::post('forgot-password', [AuthController::class, 'forgotPassword'])->name('forgot');
    Route::post('reset-password', [AuthController::class, 'resetPassword'])->name('reset');
    Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum')->name('logout');
    Route::get('user', [AuthController::class, 'user'])->middleware('auth:sanctum')->name('user');
    Route::put('profile', [AuthController::class, 'updateProfile'])->middleware('auth:sanctum')->name('profile');
});

Route::prefix('api/v1')->group(function () {
    Route::get('settings/public', PublicSettingController::class);
    Route::get('search', SearchController::class)->middleware('throttle:30,1');
    Route::post('payments/wave/webhook', [PaymentWebhookController::class, 'wave']);
    Route::post('payments/orange-money/webhook', [PaymentWebhookController::class, 'orangeMoney']);
});

Route::prefix('api/v1')->middleware('auth:sanctum')->group(function () {
    // Utilisateurs
    Route::apiResource('users', UserController::class);
    
    // Organizations
    Route::apiResource('organizations', OrganizationController::class);
    
    // Notifications
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::post('{id}/read', [NotificationController::class, 'markAsRead'])->name('read');
        Route::post('mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
        Route::get('preferences', [NotificationPreferenceController::class, 'index'])->name('preferences');
        Route::put('preferences', [NotificationPreferenceController::class, 'update'])->name('preferences.update');
    });

    Route::post('media', [MediaController::class, 'store']);
    Route::post('notifications/push-subscriptions', [PushSubscriptionController::class, 'store']);
    Route::delete('notifications/push-subscriptions', [PushSubscriptionController::class, 'destroy']);
});
