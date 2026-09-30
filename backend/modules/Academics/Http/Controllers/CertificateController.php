<?php

namespace Modules\Academics\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Academics\Models\Certificate;

class CertificateController extends Controller
{
    public function index()
    {
        $certificates = Certificate::query()
            ->with(['student:id,first_name,last_name', 'milestone:id,slug,title_i18n'])
            ->orderByDesc('issued_at')
            ->get()
            ->map(fn (Certificate $certificate) => [
                'id' => $certificate->id,
                'student_name' => $certificate->student?->fullName() ?: 'Étudiant',
                'level' => $certificate->level_label_i18n,
                'milestone_slug' => $certificate->milestone?->slug,
                'verification_code' => $certificate->verification_code,
                'issued_at' => $certificate->issued_at?->toISOString(),
                'document_url' => $certificate->document_path
                    ? url('storage/'.$certificate->document_path)
                    : null,
                'kind' => 'attestation_de_niveau',
                'is_ijaza' => false,
            ]);

        return response()->json(['data' => $certificates]);
    }

    public function verify(string $code)
    {
        $certificate = Certificate::with(['student', 'milestone'])
            ->where('verification_code', $code)
            ->firstOrFail();

        return response()->json([
            'kind' => 'attestation_de_niveau',
            'is_ijaza' => false,
            'student' => $certificate->student->name,
            'level' => $certificate->level_label_i18n,
            'issued_at' => $certificate->issued_at->toISOString(),
            'document_url' => $certificate->document_path ? url('storage/' . $certificate->document_path) : null,
            'verification_code' => $certificate->verification_code,
        ]);
    }
}
