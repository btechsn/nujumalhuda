<?php

namespace Modules\Academics\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Academics\Models\Ijaza;

class IjazaController extends Controller
{
    public function index()
    {
        $ijazas = Ijaza::query()
            ->with([
                'student:id,first_name,last_name',
                'teacher:id,first_name,last_name',
            ])
            ->where('is_public', true)
            ->orderByDesc('signed_at')
            ->get()
            ->map(fn (Ijaza $ijaza) => [
                'id' => $ijaza->id,
                'student_name' => $ijaza->student?->fullName() ?: 'Élève',
                'teacher_name' => $ijaza->teacher?->fullName() ?: 'Enseignant',
                'scope' => $ijaza->scope_i18n,
                'sanad' => $ijaza->sanad_i18n,
                'verification_code' => $ijaza->verification_code,
                'signed_at' => $ijaza->signed_at?->toISOString(),
                'document_url' => $ijaza->document_path
                    ? url('storage/'.$ijaza->document_path)
                    : null,
                'kind' => 'ijaza',
                'is_ijaza' => true,
            ]);

        return response()->json(['data' => $ijazas]);
    }

    public function verify(string $code)
    {
        $ijaza = Ijaza::with(['teacher', 'student'])
            ->where('verification_code', $code)
            ->firstOrFail();

        return response()->json([
            'kind' => 'ijaza',
            'is_ijaza' => true,
            'student' => $ijaza->student?->fullName(),
            'teacher' => $ijaza->teacher?->fullName(),
            'scope' => $ijaza->scope_i18n,
            'sanad' => $ijaza->is_public ? $ijaza->sanad_i18n : null,
            'signed_at' => $ijaza->signed_at?->toISOString(),
            'document_url' => $ijaza->document_path ? url('storage/' . $ijaza->document_path) : null,
            'verification_code' => $ijaza->verification_code,
        ]);
    }
}
