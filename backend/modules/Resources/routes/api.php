<?php

use Illuminate\Support\Facades\Route;
use Modules\Resources\Http\Controllers\DailyContentController;
use Modules\Resources\Http\Controllers\LibraryController;
use Modules\Resources\Http\Controllers\MuudRamadanController;
use Modules\Resources\Http\Controllers\RecitationController;
use Modules\Resources\Http\Controllers\ZakatController;

Route::prefix('api/v1/resources')->middleware('api')->group(function () {
    Route::get('library', [LibraryController::class, 'index']);
    Route::get('library/{slug}', [LibraryController::class, 'show']);
    Route::post('library/{slug}/download', [LibraryController::class, 'download']);

    Route::get('recitations', [RecitationController::class, 'index']);
    Route::post('recitations/{id}/play', [RecitationController::class, 'play']);

    Route::get('daily', [DailyContentController::class, 'today']);

    Route::get('zakat', [ZakatController::class, 'parameters']);
    Route::post('zakat/calculate', [ZakatController::class, 'calculate']);

    Route::get('muud-ramadan', [MuudRamadanController::class, 'parameters']);
    Route::post('muud-ramadan/calculate', [MuudRamadanController::class, 'calculate']);
});
