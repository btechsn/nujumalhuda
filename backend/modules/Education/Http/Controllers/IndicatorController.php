<?php

declare(strict_types=1);

namespace Modules\Education\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\Academics\Models\Ijaza;
use Modules\Education\Models\Program;
use Modules\Education\Models\Teacher;
use Modules\Mosque\Models\Khutba;
use Modules\Resources\Models\AudioRecitation;

class IndicatorController extends Controller
{
    /**
     * Comptes publics du centre. Aucun nom, aucun dossier.
     */
    public function __invoke(): JsonResponse
    {
        return response()->json([
            'data' => [
                'teachers' => Teacher::query()->available()->count(),
                'graduates' => (int) Ijaza::query()
                    ->where('is_public', true)
                    ->whereNotNull('signed_at')
                    ->whereNotNull('teacher_id')
                    ->distinct()
                    ->count('student_id'),
                'programs' => Program::query()->active()->count(),
                'khutbas' => Khutba::query()->published()->count(),
                'recitations' => AudioRecitation::query()->where('is_public', true)->count(),
            ],
        ]);
    }
}
