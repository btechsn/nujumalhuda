<?php

namespace Modules\Mosque\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Mosque\Services\CalendarExportService;
use Modules\Mosque\Models\MosqueEvent;

class CalendarExportController extends Controller
{
    public function __construct(
        private CalendarExportService $calendarService
    ) {}

    /**
     * Exporter les horaires de prière en iCal
     *
     * @param Request $request
     * @return Response
     */
    public function exportPrayerTimes(Request $request): Response
    {
        $validated = $request->validate([
            'start_date' => 'required|date|date_format:Y-m-d',
            'end_date' => 'required|date|date_format:Y-m-d|after_or_equal:start_date',
            'organization_id' => 'nullable|string|exists:organizations,id',
        ]);

        $icalContent = $this->calendarService->generatePrayerTimesIcal(
            $validated['start_date'],
            $validated['end_date'],
            $validated['organization_id'] ?? null
        );

        $filename = 'nujum-al-huda-prayer-times-' . now()->format('Y-m-d') . '.ics';

        return response($icalContent, 200)
            ->header('Content-Type', 'text/calendar; charset=utf-8')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"")
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    /**
     * Exporter les événements de la mosquée en iCal
     *
     * @param Request $request
     * @return Response
     */
    public function exportMosqueEvents(Request $request): Response
    {
        $validated = $request->validate([
            'organization_id' => 'nullable|string|exists:organizations,id',
            'upcoming_only' => 'nullable|boolean',
        ]);

        $icalContent = $this->calendarService->generateMosqueEventsIcal(
            $validated['organization_id'] ?? null,
            $validated['upcoming_only'] ?? true
        );

        $filename = 'nujum-al-huda-events-' . now()->format('Y-m-d') . '.ics';

        return response($icalContent, 200)
            ->header('Content-Type', 'text/calendar; charset=utf-8')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"")
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    /**
     * Obtenir l'URL Google Calendar pour un événement
     *
     * @param string $id Event ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function googleCalendarUrl(string $id)
    {
        $event = MosqueEvent::findOrFail($id);

        $url = $this->calendarService->generateGoogleCalendarUrl($event);

        return response()->json([
            'data' => [
                'url' => $url,
                'event_id' => $event->id,
                'event_title' => $event->title_i18n,
            ],
        ]);
    }
}
