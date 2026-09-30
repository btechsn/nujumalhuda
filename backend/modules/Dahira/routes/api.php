<?php

use Illuminate\Support\Facades\Route;
use Modules\Dahira\Http\Controllers\DahiraController;
use Modules\Dahira\Http\Controllers\DahiraDiscussionController;
use Modules\Dahira\Http\Controllers\DahiraFeedController;

Route::prefix('api/v1/dahira')->middleware('api')->group(function () {
    Route::get('board', [DahiraFeedController::class, 'board']);
    Route::post('groups/{groupId}/join-requests', [DahiraFeedController::class, 'requestJoin']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('groups', [DahiraController::class, 'index']);
        Route::get('groups/{id}', [DahiraController::class, 'show']);
        Route::get('groups/{id}/members', [DahiraController::class, 'members']);
        Route::post('groups/{id}/join', [DahiraController::class, 'join']);
        Route::get('groups/{id}/schedules', [DahiraController::class, 'schedules']);
        Route::post('schedules/{scheduleId}/pay', [DahiraController::class, 'pay']);
        Route::get('groups/{id}/treasury', [DahiraController::class, 'treasury']);
        Route::post('groups/{id}/expenses', [DahiraController::class, 'expense']);
        Route::get('groups/{id}/meetings', [DahiraController::class, 'meetings']);
        Route::post('groups/{id}/meetings', [DahiraController::class, 'storeMeeting']);
        Route::post('meetings/{meetingId}/attendance', [DahiraController::class, 'attendance']);
        Route::post('groups/{id}/announcements', [DahiraController::class, 'announce']);

        Route::get('groups/{groupId}/discussions', [DahiraDiscussionController::class, 'index']);
        Route::post('groups/{groupId}/discussions', [DahiraDiscussionController::class, 'store']);
        Route::get('discussions/{discussionId}/messages', [DahiraDiscussionController::class, 'messages']);
        Route::post('discussions/{discussionId}/messages', [DahiraDiscussionController::class, 'post']);
        Route::post('messages/{messageId}/hide', [DahiraDiscussionController::class, 'hide']);
    });
});
