<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Announcements\Http\Controllers\Api\V1\AnnouncementController;

/*
|--------------------------------------------------------------------------
| Announcements API Routes
|--------------------------------------------------------------------------
*/

Route::prefix('api/v1/announcements')->middleware('api')->name('announcements.')->group(function () {
    // Publique : liste des annonces visibles
    Route::get('/', [AnnouncementController::class, 'index'])->name('index');
    
    // Authentifié : marquer comme lu
    Route::post('{id}/read', [AnnouncementController::class, 'markAsRead'])
        ->middleware('auth:sanctum')
        ->name('read');
});
