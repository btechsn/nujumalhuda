<?php

use Illuminate\Support\Facades\Route;
use Modules\Mosque\Http\Controllers\CalendarExportController;
use Modules\Mosque\Http\Controllers\EventController;
use Modules\Mosque\Http\Controllers\HijriCalendarController;
use Modules\Mosque\Http\Controllers\KhutbaController;
use Modules\Mosque\Http\Controllers\PrayerTimeController;

Route::prefix('api/v1/mosque')->middleware('api')->group(function () {
    
    // Routes publiques
    Route::get('prayer-times', [PrayerTimeController::class, 'index']);
    
    Route::get('khutbas', [KhutbaController::class, 'index']);
    Route::get('khutbas/{id}', [KhutbaController::class, 'show']);
    
    Route::get('events', [EventController::class, 'index']);
    Route::get('events/{id}', [EventController::class, 'show']);
    Route::post('events/{id}/register', [EventController::class, 'register']);
    
    Route::get('calendar', [HijriCalendarController::class, 'show']);

    // Export calendrier
    Route::get('calendar/prayer-times/export', [CalendarExportController::class, 'exportPrayerTimes']);
    Route::get('calendar/events/export', [CalendarExportController::class, 'exportMosqueEvents']);
    Route::get('calendar/events/{id}/google', [CalendarExportController::class, 'googleCalendarUrl']);
});
