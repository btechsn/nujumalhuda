<?php

namespace Modules\Education\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Education\Models\Enrollment;
use Modules\Education\Services\EnrollmentAccess;
use Modules\Education\Services\PdfGeneratorService;

class PdfController extends Controller
{
    public function __construct(
        private PdfGeneratorService $pdfService,
        private EnrollmentAccess $access,
    ) {}

    public function downloadEnrollmentCertificate(string $enrollmentId)
    {
        $enrollment = Enrollment::findOrFail($enrollmentId);
        $this->authorizeEnrollment($enrollment);

        // Vérifier que l'inscription est approuvée
        if ($enrollment->status !== 'approved' && $enrollment->status !== 'active') {
            abort(403, 'L\'inscription doit être approuvée pour générer un certificat.');
        }

        $path = $this->pdfService->generateEnrollmentCertificate($enrollment);
        $filename = 'certificat-inscription-' . $enrollment->student_name . '.pdf';

        return response()->download($path, $filename)->deleteFileAfterSend();
    }

    /**
     * Télécharger le reçu de paiement
     */
    public function downloadPaymentReceipt(string $enrollmentId)
    {
        $enrollment = Enrollment::findOrFail($enrollmentId);
        $this->authorizeEnrollment($enrollment);

        // Vérifier que les frais sont payés
        if (!$enrollment->fees_paid) {
            abort(403, 'Les frais doivent être payés pour générer un reçu.');
        }

        $path = $this->pdfService->generatePaymentReceipt($enrollment);
        $filename = 'recu-paiement-' . $enrollment->student_name . '.pdf';

        return response()->download($path, $filename)->deleteFileAfterSend();
    }

    /**
     * Télécharger le certificat de scolarité
     */
    public function downloadSchoolCertificate(string $enrollmentId)
    {
        $enrollment = Enrollment::findOrFail($enrollmentId);
        $this->authorizeEnrollment($enrollment);

        // Vérifier que l'inscription est active
        if ($enrollment->status !== 'active') {
            abort(403, 'L\'inscription doit être active pour générer un certificat de scolarité.');
        }

        $path = $this->pdfService->generateSchoolCertificate($enrollment);
        $filename = 'certificat-scolarite-' . $enrollment->student_name . '.pdf';

        return response()->download($path, $filename)->deleteFileAfterSend();
    }

    private function authorizeEnrollment(Enrollment $enrollment): void
    {
        if (!$this->access->allows(auth()->user(), $enrollment)) {
            abort(403, 'Vous ne pouvez pas accéder à ce document.');
        }
    }
}
