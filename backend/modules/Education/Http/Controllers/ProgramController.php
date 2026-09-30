<?php

namespace Modules\Education\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Education\Http\Resources\ProgramResource;
use Modules\Education\Models\Program;

class ProgramController extends Controller
{
    /**
     * Liste des programmes actifs
     */
    public function index(Request $request): JsonResponse
    {
        $programs = Program::query()
            ->with(['organization', 'prerequisiteProgram'])
            ->active()
            ->when($request->type, fn($q, $type) => $q->byType($type))
            ->when($request->level, fn($q, $level) => $q->where('level', $level))
            ->when($request->featured, fn($q) => $q->featured())
            ->ordered()
            ->get();

        return response()->json([
            'data' => ProgramResource::collection($programs),
        ]);
    }

    /**
     * Détail d'un programme avec ses cours
     */
    public function show(string $id): JsonResponse
    {
        $program = Program::query()
            ->with([
                'organization',
                'prerequisiteProgram',
                'courses' => fn($q) => $q->active()->ordered(),
                'promotions' => fn($q) => $q->openForEnrollment()->orderBy('start_date'),
            ])
            ->findOrFail($id);

        return response()->json([
            'data' => new ProgramResource($program),
        ]);
    }
}
