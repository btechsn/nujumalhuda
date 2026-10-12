<?php

declare(strict_types=1);

namespace Modules\Academics\Listeners;

use Modules\Academics\Models\Certificate;
use Modules\Academics\Models\Ijaza;
use Modules\Academics\Services\IjazaDocument;
use Modules\Core\Events\MinorAnonymized;
use Modules\Core\Support\SimplePdf;

class ScrubAnonymizedStudentRecords
{
    public function handle(MinorAnonymized $event): void
    {
        $name = $event->user->fullName() ?: 'Élève anonymisé';

        Certificate::query()
            ->where('student_id', $event->user->id)
            ->with('milestone')
            ->get()
            ->each(function (Certificate $certificate) use ($name): void {
                $relative = $certificate->document_path ?: 'certificates/'.$certificate->verification_code.'.pdf';
                SimplePdf::write(storage_path('app/public/'.$relative), [
                    'Nujum Al-Huda Institute Center',
                    'Attestation de niveau',
                    'Titulaire : '.$name,
                    'Niveau : '.($certificate->level_label_i18n['fr'] ?? ''),
                    'Code : '.$certificate->verification_code,
                    'Cette attestation n est pas une ijaza.',
                ]);
                $certificate->forceFill([
                    'document_path' => $relative,
                    'is_public' => false,
                ])->save();
            });

        Ijaza::query()
            ->where('student_id', $event->user->id)
            ->get()
            ->each(function (Ijaza $ijaza): void {
                $ijaza->forceFill(['is_public' => false])->save();
                app(IjazaDocument::class)->write($ijaza->fresh(['teacher', 'student']));
            });
    }
}
