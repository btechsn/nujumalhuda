<?php

namespace Modules\Mosque\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\Models\Organization;
use Modules\Mosque\Services\PrayerTimeService;

class PrayerTimeController extends Controller
{
    public function __construct(
        private PrayerTimeService $prayerTimeService
    ) {}

    /**
     * Horaires du jour
     */
    public function index(Request $request): JsonResponse
    {
        $date = $request->input('date', now()->toDateString());
        $organizationId = $request->input('organization_id')
            ?: Organization::query()->where('slug', 'nujum-al-huda')->value('id');

        if (! is_string($organizationId) || $organizationId === '') {
            return response()->json(['message' => 'Organisation introuvable.'], 404);
        }

        $prayerTimes = $this->prayerTimeService->getPrayerTimesForDate($date, $organizationId);

        return response()->json([
            'date' => $date,
            'prayers' => $prayerTimes,
        ]);
    }
}
