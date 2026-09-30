<?php

use Illuminate\Support\Facades\Route;
use Modules\Education\Http\Controllers\EnrollmentController;
use Modules\Education\Http\Controllers\IndicatorController;
use Modules\Education\Http\Controllers\EnrollmentDocumentController;
use Modules\Education\Http\Controllers\PdfController;
use Modules\Education\Http\Controllers\ProgramController;
use Modules\Education\Http\Controllers\PromotionController;
use Modules\Education\Http\Controllers\TeacherController;

/*
|--------------------------------------------------------------------------
| Education Module API Routes
|--------------------------------------------------------------------------
*/

Route::prefix('api/v1/education')->middleware('api')->group(function () {
    
    // Routes publiques
    Route::get('indicators', IndicatorController::class);
    Route::get('programs', [ProgramController::class, 'index']);
    Route::get('programs/{id}', [ProgramController::class, 'show']);
    
    Route::get('promotions', [PromotionController::class, 'index']);
    Route::get('promotions/{id}', [PromotionController::class, 'show']);
    
    Route::get('teachers', [TeacherController::class, 'index']);
    Route::get('teachers/{id}', [TeacherController::class, 'show']);

    Route::post('enrollments/public', [EnrollmentController::class, 'storePublic']);
    
    // Routes authentifiées
    Route::middleware('auth:sanctum')->group(function () {
        
        // Inscriptions (entonnoir public)
        Route::get('enrollments', [EnrollmentController::class, 'index']);
        Route::post('enrollments', [EnrollmentController::class, 'store']);
        Route::get('enrollments/{id}', [EnrollmentController::class, 'show']);
        
        // Documents d'inscription
        Route::get('enrollments/{enrollmentId}/documents', [EnrollmentDocumentController::class, 'index']);
        Route::post('enrollments/{enrollmentId}/documents', [EnrollmentDocumentController::class, 'upload']);
        Route::get('documents/{id}/download', [EnrollmentDocumentController::class, 'download'])->name('education.documents.download');
        Route::delete('documents/{id}', [EnrollmentDocumentController::class, 'destroy']);
        
        // PDF (certificats et reçus)
        Route::get('enrollments/{enrollmentId}/certificate', [PdfController::class, 'downloadEnrollmentCertificate'])->name('education.certificate.enrollment');
        Route::get('enrollments/{enrollmentId}/receipt', [PdfController::class, 'downloadPaymentReceipt'])->name('education.receipt.payment');
        Route::get('enrollments/{enrollmentId}/school-certificate', [PdfController::class, 'downloadSchoolCertificate'])->name('education.certificate.school');
    });
});
