<?php

namespace Modules\Academics\Services;

use Illuminate\Support\Str;
use Modules\Academics\Models\Ijaza;
use Modules\Core\Support\SimplePdf;

class IjazaDocument
{
    public function write(Ijaza $ijaza): void
    {
        $ijaza->loadMissing('teacher', 'student');
        if (blank($ijaza->verification_code)) {
            $ijaza->verification_code = strtoupper(Str::random(10));
        }

        $relative = 'ijazas/' . $ijaza->verification_code . '.pdf';
        SimplePdf::write(storage_path('app/public/' . $relative), [
            'Nujum Al-Huda Institute Center',
            'Ijaza',
            'Eleve : ' . ($ijaza->student?->fullName() ?: ''),
            'Enseignant : ' . ($ijaza->teacher?->fullName() ?: ''),
            'Portee : ' . ($ijaza->scope_i18n['fr'] ?? ''),
            'Sanad : ' . ($ijaza->sanad_i18n['fr'] ?? ''),
            'Signee le ' . $ijaza->signed_at?->timezone('Africa/Dakar')->format('d/m/Y'),
            'Code : ' . $ijaza->verification_code,
        ]);

        $ijaza->forceFill([
            'verification_code' => $ijaza->verification_code,
            'document_path' => $relative,
        ])->saveQuietly();
    }
}
