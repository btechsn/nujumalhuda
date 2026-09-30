<?php

namespace Modules\Academics\Services;

use Illuminate\Support\Str;
use Modules\Academics\Models\Certificate;
use Modules\Academics\Models\StudentProgress;
use Modules\Academics\Support\SimplePdf;

class CertificateIssuer
{
    /**
     * Attestation de niveau uniquement. N'écrit jamais dans ijazas.
     */
    public function issueFor(StudentProgress $progress): Certificate
    {
        $existing = Certificate::where('progress_id', $progress->id)->first();
        if ($existing) {
            return $existing;
        }

        $progress->load('milestone', 'student');
        $localeTitle = $progress->milestone->title_i18n;

        $certificate = Certificate::create([
            'student_id' => $progress->student_id,
            'milestone_id' => $progress->milestone_id,
            'progress_id' => $progress->id,
            'verification_code' => strtoupper(Str::random(10)),
            'level_label_i18n' => $localeTitle,
            'issued_at' => now(),
        ]);

        $relative = 'certificates/' . $certificate->verification_code . '.pdf';
        $absolute = storage_path('app/public/' . $relative);
        $student = $progress->student?->fullName() ?: 'Étudiant';
        SimplePdf::write($absolute, [
            'Nujum Al-Huda Institute Center',
            'Attestation de niveau',
            'Titulaire : ' . $student,
            'Niveau : ' . ($localeTitle['fr'] ?? ''),
            'Code : ' . $certificate->verification_code,
            'Delivree le ' . $certificate->issued_at->timezone('Africa/Dakar')->format('d/m/Y'),
            'Cette attestation n est pas une ijaza.',
        ]);
        $certificate->update(['document_path' => $relative]);

        return $certificate;
    }
}
