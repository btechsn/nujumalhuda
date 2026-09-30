<?php

namespace Modules\Education\Services;

use Modules\Core\Support\SimplePdf;
use Modules\Education\Models\Enrollment;

class PdfGeneratorService
{
    public function generateEnrollmentCertificate(Enrollment $enrollment): string
    {
        $enrollment->loadMissing('promotion.program', 'user');

        return $this->write('certificates', 'certificat-inscription-' . $enrollment->id . '.pdf', [
            'Nujum Al-Huda Institute Center',
            'Certificat d inscription',
            'Eleve : ' . $enrollment->student_name,
            'Programme : ' . ($enrollment->promotion->program->name_i18n['fr'] ?? 'Programme'),
            'Promotion : ' . ($enrollment->promotion->name_i18n['fr'] ?? 'Promotion'),
            'Annee : ' . ($enrollment->promotion->academic_year ?? ''),
            'Delivre le ' . now()->timezone('Africa/Dakar')->format('d/m/Y'),
        ]);
    }

    public function generatePaymentReceipt(Enrollment $enrollment): string
    {
        $enrollment->loadMissing('promotion.program', 'user');
        $program = $enrollment->promotion->program;
        $tuition = (int) ($program->tuition_amount_minor ?? 0);
        $registration = (int) ($program->registration_amount_minor ?? 0);

        return $this->write('receipts', 'recu-paiement-' . $enrollment->id . '.pdf', [
            'Nujum Al-Huda Institute Center',
            'Recu de paiement',
            'Eleve : ' . $enrollment->student_name,
            'E-mail : ' . ($enrollment->student_email ?? ''),
            'Scolarite : ' . $tuition . ' ' . ($program->currency ?? 'XOF'),
            'Inscription : ' . $registration . ' ' . ($program->currency ?? 'XOF'),
            'Total : ' . ($tuition + $registration) . ' ' . ($program->currency ?? 'XOF'),
            'Numero : REC-' . strtoupper(substr($enrollment->id, 0, 8)),
            'Delivre le ' . now()->timezone('Africa/Dakar')->format('d/m/Y'),
        ]);
    }

    public function generateSchoolCertificate(Enrollment $enrollment): string
    {
        $enrollment->loadMissing('promotion.program', 'user');

        return $this->write('certificates', 'certificat-scolarite-' . $enrollment->id . '.pdf', [
            'Nujum Al-Huda Institute Center',
            'Certificat de scolarite',
            'Eleve : ' . $enrollment->student_name,
            'Programme : ' . ($enrollment->promotion->program->name_i18n['fr'] ?? 'Programme'),
            'Promotion : ' . ($enrollment->promotion->name_i18n['fr'] ?? 'Promotion'),
            'Assiduite : ' . ($enrollment->attendance_rate ?? 0) . ' %',
            'Delivre le ' . now()->timezone('Africa/Dakar')->format('d/m/Y'),
        ]);
    }

    /**
     * @param  list<string>  $lines
     */
    private function write(string $directory, string $filename, array $lines): string
    {
        $path = storage_path('app/private/' . $directory . '/' . $filename);
        SimplePdf::write($path, $lines);

        return $path;
    }
}
