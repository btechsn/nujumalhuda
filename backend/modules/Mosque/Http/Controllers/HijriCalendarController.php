<?php

namespace Modules\Mosque\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\Mosque\Services\HijriCalendarService;

class HijriCalendarController extends Controller
{
    public function __construct(private readonly HijriCalendarService $calendar)
    {
    }

    public function show(): JsonResponse
    {
        $today = now()->timezone('Africa/Dakar')->toDateString();

        return response()->json([
            'today' => $this->calendar->getHijriDate($today),
            'days_until_ramadan' => $this->calendar->getDaysUntilRamadan(),
            'is_jumua' => $this->calendar->isJumuaDay(),
            'important_dates' => $this->calendar->getImportantDates(),
        ]);
    }
}
