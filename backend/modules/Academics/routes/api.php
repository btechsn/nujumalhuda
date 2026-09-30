<?php

use Illuminate\Support\Facades\Route;
use Modules\Academics\Http\Controllers\CertificateController;
use Modules\Academics\Http\Controllers\IjazaController;
use Modules\Academics\Http\Controllers\ProgressController;
use Modules\Academics\Http\Controllers\QuizController;

Route::prefix('api/v1/academics')->middleware('api')->group(function () {
    Route::get('milestones', [ProgressController::class, 'milestones']);
    Route::get('quizzes', [QuizController::class, 'index']);
    Route::get('quizzes/{slug}', [QuizController::class, 'show']);
    Route::post('quizzes/{slug}/practice', [QuizController::class, 'practice']);
    Route::get('certificates', [CertificateController::class, 'index']);
    Route::get('certificates/{code}', [CertificateController::class, 'verify']);
    Route::get('ijazas', [IjazaController::class, 'index']);
    Route::get('ijazas/{code}', [IjazaController::class, 'verify']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('portal', [ProgressController::class, 'portal']);
        Route::get('students/{studentId}/progress', [ProgressController::class, 'show']);
        Route::post('quizzes/{slug}/attempts', [QuizController::class, 'attempt']);
    });
});
