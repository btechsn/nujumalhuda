<?php

use Illuminate\Support\Facades\Route;
use Modules\Live\Http\Controllers\LiveChatController;
use Modules\Live\Http\Controllers\LiveStreamController;
use Modules\Live\Http\Controllers\MediaMtxWebhookController;
use Modules\Live\Http\Controllers\RecitationSessionController;
use Modules\Live\Http\Controllers\SocialAccountController;
use Modules\Live\Http\Controllers\VodController;

/*
|--------------------------------------------------------------------------
| Live Module API Routes
|--------------------------------------------------------------------------
*/

Route::prefix('api/v1')->middleware('api')->group(function () {
    
    // Live Streams
    Route::prefix('live')->group(function () {
        Route::get('social-accounts', [SocialAccountController::class, 'index']);
        Route::get('recitation-sessions', [RecitationSessionController::class, 'index']);
        Route::middleware('auth:sanctum')->post('recitation-sessions', [RecitationSessionController::class, 'store']);

        Route::get('streams', [LiveStreamController::class, 'index']);
        Route::get('streams/live', [LiveStreamController::class, 'live']);
        Route::get('streams/upcoming', [LiveStreamController::class, 'upcoming']);
        Route::get('streams/{id}', [LiveStreamController::class, 'show']);
        Route::post('streams/{id}/join', [LiveStreamController::class, 'join']);
        Route::post('streams/{id}/leave', [LiveStreamController::class, 'leave']);
        Route::get('streams/{id}/health', [LiveStreamController::class, 'health']);

        // Admin routes
        Route::middleware('auth:sanctum')->group(function () {
            Route::post('streams', [LiveStreamController::class, 'store']);
            Route::put('streams/{id}', [LiveStreamController::class, 'update']);
            Route::delete('streams/{id}', [LiveStreamController::class, 'destroy']);
            Route::post('streams/{id}/rotate-key', [LiveStreamController::class, 'rotateKey']);
        });
    });

    // Live Chat
    Route::prefix('live/streams/{streamId}/chat')->group(function () {
        Route::get('/', [LiveChatController::class, 'index']);
        Route::post('/', [LiveChatController::class, 'store']);
        Route::post('reactions', [LiveChatController::class, 'reaction']);
        Route::get('stats', [LiveChatController::class, 'stats']);

        Route::middleware('auth:sanctum')->group(function () {
            Route::post('messages/{messageId}/moderate', [LiveChatController::class, 'moderate']);
            Route::post('messages/{messageId}/report', [LiveChatController::class, 'report']);
        });
    });

    Route::middleware('auth:sanctum')->get('live/chat/flagged', [LiveChatController::class, 'flagged']);

    // VOD (Video On Demand)
    Route::prefix('vod')->group(function () {
        Route::get('recordings', [VodController::class, 'index']);
        Route::get('recordings/popular', [VodController::class, 'popular']);
        Route::get('recordings/{slug}', [VodController::class, 'show']);
        Route::get('recordings/{slug}/related', [VodController::class, 'related']);
    });

    // MediaMTX : auth HTTP (chaque publish/read) et hooks de cycle de vie
    Route::match(['get', 'post'], 'webhooks/mediamtx/auth', [MediaMtxWebhookController::class, 'auth']);
    Route::post('webhooks/mediamtx', [MediaMtxWebhookController::class, 'handle']);
});
