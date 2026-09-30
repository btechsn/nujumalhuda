<?php

namespace Modules\Mosque\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\Live\Models\LiveStream;
use Modules\Mosque\Http\Resources\KhutbaResource;
use Modules\Mosque\Models\Khutba;

class KhutbaController extends Controller
{
    public function index(): JsonResponse
    {
        $khutbas = Khutba::query()
            ->with(['speaker', 'audio', 'video'])
            ->published()
            ->recent(20)
            ->get();

        return response()->json([
            'data' => KhutbaResource::collection($khutbas),
        ]);
    }

    public function show(string $id): JsonResponse
    {
        $khutba = Khutba::query()
            ->with(['speaker', 'audio', 'video', 'organization'])
            ->published()
            ->findOrFail($id);

        $khutba->related_live = $this->findRelatedLive($khutba);

        return response()->json([
            'data' => new KhutbaResource($khutba),
        ]);
    }

    private function findRelatedLive(Khutba $khutba): ?LiveStream
    {
        if (! $khutba->date) {
            return null;
        }

        $day = $khutba->date->toDateString();

        return LiveStream::query()
            ->with('recording')
            ->where('type', 'khutba')
            ->where(function ($query) use ($day) {
                $query->whereDate('scheduled_at', $day)
                    ->orWhereDate('started_at', $day);
            })
            ->orderByRaw("CASE status WHEN 'live' THEN 0 WHEN 'ended' THEN 1 WHEN 'archived' THEN 2 ELSE 3 END")
            ->orderByDesc('scheduled_at')
            ->first();
    }
}
