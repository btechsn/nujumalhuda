<?php

use Illuminate\Support\Facades\Route;
use Modules\Community\Http\Controllers\CommunityController;
use Modules\Community\Http\Controllers\CommunityFeedController;
use Modules\Community\Http\Controllers\DiscussionController;

Route::prefix('api/v1/community')->middleware('api')->group(function () {
    Route::get('board', [CommunityFeedController::class, 'board']);
    Route::get('donors', [CommunityController::class, 'donors']);
    Route::post('donations', [CommunityController::class, 'donate']);
    Route::get('testimonials', [CommunityController::class, 'testimonials']);
    Route::get('partners', [CommunityController::class, 'partners']);
    Route::get('gallery', [CommunityController::class, 'gallery']);
    Route::get('questions', [CommunityController::class, 'questions']);
    Route::get('questions/{id}', [CommunityController::class, 'question']);
    Route::get('events', [CommunityController::class, 'events']);
    Route::post('events/{eventId}/registrations', [CommunityController::class, 'register']);
    Route::post('sms-digest', [CommunityController::class, 'subscribeDigest']);
    Route::post('newsletter', [CommunityController::class, 'subscribeNewsletter']);
    Route::post('contact', [CommunityController::class, 'contact']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('questions', [CommunityController::class, 'ask']);
        Route::get('discussions', [DiscussionController::class, 'index']);
        Route::post('discussions', [DiscussionController::class, 'store']);
        Route::get('discussions/{id}/messages', [DiscussionController::class, 'messages']);
        Route::post('discussions/{id}/messages', [DiscussionController::class, 'post']);
        Route::post('messages/{messageId}/hide', [DiscussionController::class, 'hide']);
    });
});
