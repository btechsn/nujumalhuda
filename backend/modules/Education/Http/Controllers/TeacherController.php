<?php

namespace Modules\Education\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\Education\Http\Resources\TeacherResource;
use Modules\Education\Models\Teacher;

class TeacherController extends Controller
{
    /**
     * Liste des enseignants (page publique)
     */
    public function index(): JsonResponse
    {
        $teachers = Teacher::query()
            ->with(['user', 'photo', 'organization'])
            ->available()
            ->ordered()
            ->get();

        return response()->json([
            'data' => TeacherResource::collection($teachers),
        ]);
    }

    /**
     * Détail d'un enseignant avec sa biographie
     */
    public function show(string $id): JsonResponse
    {
        $teacher = Teacher::query()
            ->with(['user', 'photo', 'organization'])
            ->findOrFail($id);

        return response()->json([
            'data' => new TeacherResource($teacher),
        ]);
    }
}
