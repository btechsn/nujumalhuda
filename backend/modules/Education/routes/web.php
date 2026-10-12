<?php

use Illuminate\Support\Facades\Route;
use Modules\Education\Http\Controllers\EnrollmentDocumentController;

Route::middleware(['web', 'auth'])->get(
    'pieces-inscription/{document}',
    [EnrollmentDocumentController::class, 'downloadForStaff'],
)->name('education.admin.documents.download');
