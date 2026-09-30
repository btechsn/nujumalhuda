<?php

namespace Modules\Mosque\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Modules\Mosque\Models\HijriObservance;
use Modules\Mosque\Support\HijriDate;

class HijriCalendarService
{
    /**
     * Obtenir la date hégirique pour une date grégorienne
     */
    public function getHijriDate(string $gregorianDate): ?array
    {
        $cacheKey = 'hijri_date:' . Cache::get('hijri.revision', '0') . ':' . $gregorianDate;

        return Cache::remember($cacheKey, now()->addHour(), function () use ($gregorianDate) {
            return $this->observedHijri($gregorianDate) ?? $this->fetchHijriDate($gregorianDate);
        });
    }

    /**
     * Appeler l'API Aladhan pour obtenir la date hégirique
     */
    private function fetchHijriDate(string $gregorianDate): ?array
    {
        try {
            $response = Http::timeout(8)->get('http://api.aladhan.com/v1/gToH/' . $gregorianDate);
        } catch (\Throwable $exception) {
            return $this->calculatedHijri($gregorianDate);
        }

        if (!$response->successful()) {
            return $this->calculatedHijri($gregorianDate);
        }

        $data = $response->json('data.hijri');

        if (!$data) {
            return $this->calculatedHijri($gregorianDate);
        }

        return [
            'day' => $data['day'] ?? null,
            'month' => [
                'number' => $data['month']['number'] ?? null,
                'en' => $data['month']['en'] ?? null,
                'ar' => $data['month']['ar'] ?? null,
            ],
            'year' => $data['year'] ?? null,
            'formatted' => $data['day'] . ' ' . ($data['month']['ar'] ?? '') . ' ' . $data['year'],
            'source' => 'aladhan',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function calculatedHijri(string $gregorianDate): array
    {
        $date = Carbon::parse($gregorianDate, 'Africa/Dakar');
        $hijri = HijriDate::fromGregorian((int) $date->year, (int) $date->month, (int) $date->day);

        return [
            'day' => (string) $hijri['day'],
            'month' => [
                'number' => $hijri['month'],
                'en' => HijriDate::monthName($hijri['month'], 'en'),
                'ar' => HijriDate::monthName($hijri['month'], 'ar'),
                'fr' => HijriDate::monthName($hijri['month'], 'fr'),
            ],
            'year' => (string) $hijri['year'],
            'formatted' => $hijri['day'] . ' ' . HijriDate::monthName($hijri['month'], 'ar') . ' ' . $hijri['year'],
            'source' => 'tabular',
        ];
    }

    /**
     * Obtenir le compte à rebours jusqu'au Ramadan
     */
    public function getDaysUntilRamadan(): int
    {
        $today = Carbon::today('Africa/Dakar');
        $start = $this->nextRamadanStart($today);

        if ($start->isSameDay($today) || $this->isDuringRamadan($today)) {
            return 0;
        }

        return (int) $today->diffInDays($start);
    }

    public function nextRamadanStart(?Carbon $today = null): Carbon
    {
        $today ??= Carbon::today('Africa/Dakar');
        $observed = HijriObservance::query()
            ->where('kind', 'ramadan_start')
            ->whereDate('gregorian_date', '>=', $today->toDateString())
            ->orderBy('gregorian_date')
            ->first();

        if ($observed) {
            return $observed->gregorian_date->copy()->timezone('Africa/Dakar')->startOfDay();
        }

        $hijri = HijriDate::fromGregorian((int) $today->year, (int) $today->month, (int) $today->day);
        $year = $hijri['year'];
        if ($hijri['month'] > 9 || $this->isDuringRamadan($today)) {
            $year++;
        }

        $gregorian = HijriDate::toGregorian($year, 9, 1);

        return Carbon::create($gregorian['year'], $gregorian['month'], $gregorian['day'], 0, 0, 0, 'Africa/Dakar');
    }

    private function isDuringRamadan(Carbon $today): bool
    {
        $start = HijriObservance::query()
            ->where('kind', 'ramadan_start')
            ->whereDate('gregorian_date', '<=', $today->toDateString())
            ->orderByDesc('gregorian_date')
            ->first();

        if ($start) {
            $end = HijriObservance::query()
                ->where('kind', 'eid_al_fitr')
                ->where('hijri_year', $start->hijri_year)
                ->first();
            $inside = $end
                ? $today->toDateString() < $end->gregorian_date->toDateString()
                : $start->gregorian_date->diff($today)->days < 30;

            if ($inside) {
                return true;
            }
        }

        $hijri = HijriDate::fromGregorian((int) $today->year, (int) $today->month, (int) $today->day);

        return $hijri['month'] === 9;
    }

    /**
     * Vérifier si aujourd'hui est un vendredi (Jumu'a)
     */
    public function isJumuaDay(): bool
    {
        return now()->isFriday();
    }

    /**
     * Obtenir les dates importantes du calendrier hégirique
     */
    public function getImportantDates(int $year = null): array
    {
        $today = Carbon::today('Africa/Dakar');
        $current = HijriDate::fromGregorian((int) $today->year, (int) $today->month, (int) $today->day);
        $hijriYear = $year ?? $current['year'];

        return [
            'ramadan_start' => $this->importantDate($hijriYear, 'ramadan_start', 9, 1),
            'eid_al_fitr' => $this->importantDate($hijriYear, 'eid_al_fitr', 10, 1),
            'eid_al_adha' => $this->importantDate($hijriYear, 'eid_al_adha', 12, 10),
            'hijri_new_year' => $this->importantDate($hijriYear + 1, 'hijri_new_year', 1, 1),
            'calendar' => HijriObservance::query()->exists() ? 'observation' : 'tabular',
        ];
    }

    /**
     * @return array{gregorian: string, hijri: string, source: string}
     */
    private function importantDate(int $year, string $kind, int $month, int $day): array
    {
        $observed = HijriObservance::query()
            ->where('hijri_year', $year)
            ->where('kind', $kind)
            ->first();

        if ($observed) {
            return [
                'gregorian' => $observed->gregorian_date->toDateString(),
                'hijri' => $observed->label(),
                'source' => 'observation',
            ];
        }

        $gregorian = HijriDate::toGregorian($year, $month, $day);

        return [
            'gregorian' => sprintf('%04d-%02d-%02d', $gregorian['year'], $gregorian['month'], $gregorian['day']),
            'hijri' => $day . ' ' . HijriDate::monthName($month, 'fr') . ' ' . $year,
            'source' => 'tabular',
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function observedHijri(string $gregorianDate): ?array
    {
        $day = Carbon::parse($gregorianDate, 'Africa/Dakar')->startOfDay();
        $anchor = HijriObservance::query()
            ->whereDate('gregorian_date', '<=', $day->toDateString())
            ->orderByDesc('gregorian_date')
            ->first();

        if (!$anchor) {
            return null;
        }

        $elapsed = (int) $anchor->gregorian_date->copy()->startOfDay()->diff($day)->days;
        $hijriDay = $anchor->hijriDay() + $elapsed;
        if ($hijriDay < 1 || $hijriDay > 30) {
            return null;
        }

        $month = $anchor->hijriMonth();

        return [
            'day' => (string) $hijriDay,
            'month' => [
                'number' => $month,
                'en' => HijriDate::monthName($month, 'en'),
                'ar' => HijriDate::monthName($month, 'ar'),
                'fr' => HijriDate::monthName($month, 'fr'),
            ],
            'year' => (string) $anchor->hijri_year,
            'formatted' => $hijriDay . ' ' . HijriDate::monthName($month, 'ar') . ' ' . $anchor->hijri_year,
            'source' => 'observation',
        ];
    }
}
