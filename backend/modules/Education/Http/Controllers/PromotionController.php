<?php

namespace Modules\Education\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Education\Http\Resources\PromotionResource;
use Modules\Education\Models\Promotion;

class PromotionController extends Controller
{
    /**
     * Liste des promotions ouvertes
     */
    public function index(Request $request): JsonResponse
    {
        $promotions = Promotion::query()
            ->with(['program', 'mainTeacher', 'organization'])
            ->when($request->status, fn($q, $status) => $q->where('status', $status))
            ->when($request->open_only, fn($q) => $q->openForEnrollment())
            ->when($request->year, fn($q, $year) => $q->byYear($year))
            ->orderBy('start_date', 'desc')
            ->get();

        return response()->json([
            'data' => PromotionResource::collection($promotions),
        ]);
    }

    /**
     * Détail d'une promotion avec sessions planifiées
     */
    public function show(string $id): JsonResponse
    {
        $promotion = Promotion::query()
            ->with([
                'program.courses',
                'mainTeacher',
                'organization',
                'sessions' => fn($q) => $q->upcoming()->limit(10),
            ])
            ->findOrFail($id);

        return response()->json([
            'data' => new PromotionResource($promotion),
        ]);
    }
}
