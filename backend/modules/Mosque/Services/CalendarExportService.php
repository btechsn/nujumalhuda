<?php

namespace Modules\Mosque\Services;

use Carbon\Carbon;
use Modules\Mosque\Models\PrayerTime;
use Modules\Mosque\Models\MosqueEvent;

class CalendarExportService
{
    /**
     * Générer un fichier iCal pour les horaires de prière
     *
     * @param string $startDate Format: Y-m-d
     * @param string $endDate Format: Y-m-d
     * @param string|null $organizationId
     * @return string Contenu iCal
     */
    public function generatePrayerTimesIcal(
        string $startDate,
        string $endDate,
        ?string $organizationId = null
    ): string {
        $query = PrayerTime::whereBetween('date', [$startDate, $endDate]);
        
        if ($organizationId) {
            $query->where('organization_id', $organizationId);
        }

        $prayerTimes = $query->orderBy('date')->orderBy('prayer_name')->get();

        $ical = $this->generateIcalHeader();

        foreach ($prayerTimes as $prayer) {
            $ical .= $this->generatePrayerTimeEvent($prayer);
        }

        $ical .= $this->generateIcalFooter();

        return $ical;
    }

    /**
     * Générer un fichier iCal pour les événements de la mosquée
     *
     * @param string|null $organizationId
     * @param bool $upcomingOnly Ne récupérer que les événements à venir
     * @return string Contenu iCal
     */
    public function generateMosqueEventsIcal(
        ?string $organizationId = null,
        bool $upcomingOnly = true
    ): string {
        $query = MosqueEvent::query();

        if ($organizationId) {
            $query->where('organization_id', $organizationId);
        }

        if ($upcomingOnly) {
            $query->where('start_at', '>=', now());
        }

        $events = $query->orderBy('start_at')->get();

        $ical = $this->generateIcalHeader();

        foreach ($events as $event) {
            $ical .= $this->generateMosqueEventEvent($event);
        }

        $ical .= $this->generateIcalFooter();

        return $ical;
    }

    /**
     * En-tête iCal
     */
    private function generateIcalHeader(): string
    {
        return implode("\r\n", [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//Nujum Al-Huda Institute//Prayer Times//FR',
            'CALSCALE:GREGORIAN',
            'METHOD:PUBLISH',
            'X-WR-CALNAME:Nujum Al-Huda - Horaires de prière',
            'X-WR-TIMEZONE:Africa/Dakar',
            'X-WR-CALDESC:Horaires de prière quotidiens de la mosquée Nujum Al-Huda',
        ]) . "\r\n";
    }

    /**
     * Pied de page iCal
     */
    private function generateIcalFooter(): string
    {
        return "END:VCALENDAR\r\n";
    }

    /**
     * Événement iCal pour un horaire de prière
     */
    private function generatePrayerTimeEvent(PrayerTime $prayer): string
    {
        $date = Carbon::parse($prayer->date);
        $time = Carbon::parse($prayer->display_time);
        
        $datetime = $date->setTimeFromTimeString($time->format('H:i:s'));
        
        // Format iCal : YYYYMMDDTHHMMSS
        $dtstart = $datetime->format('Ymd\THis');
        $dtend = $datetime->copy()->addMinutes(30)->format('Ymd\THis'); // 30 min par défaut
        
        $uid = "prayer-{$prayer->id}@nujumalhuda.com";
        $dtstamp = now()->format('Ymd\THis\Z');
        
        $prayerNames = [
            'fajr' => 'Fajr (Aube)',
            'dhuhr' => 'Dhuhr (Midi)',
            'asr' => 'Asr (Après-midi)',
            'maghrib' => 'Maghrib (Coucher)',
            'isha' => 'Isha (Nuit)',
        ];
        
        $summary = $prayerNames[$prayer->prayer_name] ?? $prayer->prayer_name;
        $description = "Horaire de prière : {$summary}\\n";
        $description .= "Iqama : {$prayer->iqama_time}\\n";
        
        if ($prayer->is_overridden) {
            $description .= "Horaire modifié manuellement par l'imam.";
        }

        return implode("\r\n", [
            'BEGIN:VEVENT',
            "UID:{$uid}",
            "DTSTAMP:{$dtstamp}",
            "DTSTART;TZID=Africa/Dakar:{$dtstart}",
            "DTEND;TZID=Africa/Dakar:{$dtend}",
            "SUMMARY:{$summary}",
            "DESCRIPTION:{$description}",
            "LOCATION:Mosquée Nujum Al-Huda\\, Dakar\\, Sénégal",
            'STATUS:CONFIRMED',
            'TRANSP:OPAQUE',
            'CATEGORIES:Prière',
            'END:VEVENT',
        ]) . "\r\n";
    }

    /**
     * Événement iCal pour un événement de la mosquée
     */
    private function generateMosqueEventEvent(MosqueEvent $event): string
    {
        $startAt = Carbon::parse($event->start_at);
        $endAt = Carbon::parse($event->end_at);
        
        $dtstart = $startAt->format('Ymd\THis');
        $dtend = $endAt->format('Ymd\THis');
        
        $uid = "event-{$event->id}@nujumalhuda.com";
        $dtstamp = now()->format('Ymd\THis\Z');
        
        $title = $event->title_i18n['fr'] ?? $event->title_i18n['en'] ?? 'Événement';
        $description = $event->description_i18n['fr'] ?? $event->description_i18n['en'] ?? '';
        
        // Supprimer les balises HTML de la description
        $description = strip_tags($description);
        $description = str_replace(["\r\n", "\n", "\r"], '\\n', $description);
        
        $location = $event->location ?? 'Mosquée Nujum Al-Huda, Dakar, Sénégal';

        $lines = [
            'BEGIN:VEVENT',
            "UID:{$uid}",
            "DTSTAMP:{$dtstamp}",
            "DTSTART;TZID=Africa/Dakar:{$dtstart}",
            "DTEND;TZID=Africa/Dakar:{$dtend}",
            "SUMMARY:{$title}",
            "DESCRIPTION:{$description}",
            "LOCATION:{$location}",
            'STATUS:CONFIRMED',
            'TRANSP:OPAQUE',
        ];

        // Ajouter catégorie selon le type
        $categories = [
            'lecture' => 'Conférence',
            'workshop' => 'Atelier',
            'fundraising' => 'Collecte',
            'special_prayer' => 'Prière spéciale',
            'community' => 'Communauté',
            'gamou' => 'Gamou',
        ];
        
        if (isset($categories[$event->type])) {
            $lines[] = "CATEGORIES:{$categories[$event->type]}";
        }

        // Ajouter speaker si présent
        if ($event->speaker_name) {
            $lines[] = "ORGANIZER;CN={$event->speaker_name}:mailto:contact@nujumalhuda.com";
        }

        $lines[] = 'END:VEVENT';

        return implode("\r\n", $lines) . "\r\n";
    }

    /**
     * Générer une URL Google Calendar
     *
     * @param MosqueEvent $event
     * @return string URL Google Calendar
     */
    public function generateGoogleCalendarUrl(MosqueEvent $event): string
    {
        $title = $event->title_i18n['fr'] ?? $event->title_i18n['en'] ?? 'Événement';
        $description = strip_tags($event->description_i18n['fr'] ?? $event->description_i18n['en'] ?? '');
        $location = $event->location ?? 'Mosquée Nujum Al-Huda, Dakar, Sénégal';
        
        $startAt = Carbon::parse($event->start_at);
        $endAt = Carbon::parse($event->end_at);
        
        // Format Google Calendar: YYYYMMDDTHHmmss
        $dates = $startAt->format('Ymd\THis') . '/' . $endAt->format('Ymd\THis');
        
        $params = http_build_query([
            'action' => 'TEMPLATE',
            'text' => $title,
            'dates' => $dates,
            'details' => $description,
            'location' => $location,
            'ctz' => 'Africa/Dakar',
        ]);

        return 'https://calendar.google.com/calendar/render?' . $params;
    }
}
