<?php

namespace Modules\Mosque\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Modules\Mosque\Models\IqamaAdjustment;
use Modules\Mosque\Models\PrayerTime;

class PrayerTimeService
{
    /**
     * Coordonnées de Dakar, Sénégal
     */
    private const LATITUDE = 14.6928;
    private const LONGITUDE = -17.4467;
    private const TIMEZONE = 'Africa/Dakar';
    private const CALCULATION_METHOD = 5; // Muslim World League

    /**
     * Récupérer les horaires de prière pour une date (avec cache)
     */
    public function getPrayerTimesForDate(string $date, string $organizationId): array
    {
        $cacheKey = "prayer_times:{$organizationId}:{$date}";

        return Cache::remember($cacheKey, now()->addHours(24), function () use ($date, $organizationId) {
            return $this->fetchOrCalculatePrayerTimes($date, $organizationId);
        });
    }

    /**
     * Récupérer depuis la DB ou calculer via API Aladhan
     */
    private function fetchOrCalculatePrayerTimes(string $date, string $organizationId): array
    {
        $prayers = ['fajr', 'dhuhr', 'asr', 'maghrib', 'isha'];
        $result = [];

        foreach ($prayers as $prayer) {
            $prayerTime = PrayerTime::where('organization_id', $organizationId)
                ->where('date', $date)
                ->where('prayer_name', $prayer)
                ->first();

            if (!$prayerTime) {
                // Calculer via API Aladhan
                $prayerTime = $this->calculateAndStore($date, $prayer, $organizationId);
            }

            // Calculer l'iqama si nécessaire
            if ($prayerTime && !$prayerTime->iqama_time) {
                $this->calculateIqama($prayerTime, $date);
            }

            $result[$prayer] = [
                'id' => $prayerTime?->id,
                'name' => $prayer,
                'adhan' => $prayerTime?->getEffectiveTime(),
                'iqama' => $prayerTime?->iqama_time,
                'is_overridden' => $prayerTime?->is_overridden ?? false,
            ];
        }

        return $result;
    }

    /**
     * Calculer via API Aladhan et stocker
     */
    private function calculateAndStore(string $date, string $prayer, string $organizationId): ?PrayerTime
    {
        $times = $this->fetchFromAladhanAPI($date);

        if (!isset($times[$prayer])) {
            return null;
        }

        return PrayerTime::create([
            'organization_id' => $organizationId,
            'date' => $date,
            'prayer_name' => $prayer,
            'calculated_time' => $times[$prayer],
            'calculation_method' => 'MWL',
            'is_overridden' => false,
        ]);
    }

    /**
     * Appeler l'API Aladhan
     */
    private function fetchFromAladhanAPI(string $date): array
    {
        $response = Http::get('http://api.aladhan.com/v1/timings/' . $date, [
            'latitude' => self::LATITUDE,
            'longitude' => self::LONGITUDE,
            'method' => self::CALCULATION_METHOD,
        ]);

        if (!$response->successful()) {
            return [];
        }

        $timings = $response->json('data.timings', []);

        return [
            'fajr' => $timings['Fajr'] ?? null,
            'dhuhr' => $timings['Dhuhr'] ?? null,
            'asr' => $timings['Asr'] ?? null,
            'maghrib' => $timings['Maghrib'] ?? null,
            'isha' => $timings['Isha'] ?? null,
        ];
    }

    /**
     * Recalculer l'iqama après un override ou un retour au calcul automatique.
     */
    public function refreshIqama(PrayerTime $prayerTime): void
    {
        $this->calculateIqama($prayerTime, $prayerTime->date->format('Y-m-d'));
    }

    /**
     * Calculer l'heure d'iqama avec décalage
     */
    private function calculateIqama(PrayerTime $prayerTime, string $date): void
    {
        $adjustment = IqamaAdjustment::where('organization_id', $prayerTime->organization_id)
            ->where('prayer_name', $prayerTime->prayer_name)
            ->active()
            ->validOn($date)
            ->first();

        $offsetMinutes = $adjustment?->minutes_offset ?? 15;

        $adhanTime = $prayerTime->getEffectiveTime();
        if ($adhanTime) {
            $iqamaTime = Carbon::parse($adhanTime)->addMinutes($offsetMinutes)->format('H:i');
            $prayerTime->update(['iqama_time' => $iqamaTime]);
        }
    }

    /**
     * Invalider le cache pour une date
     */
    public function clearCacheForDate(string $date, string $organizationId): void
    {
        Cache::forget("prayer_times:{$organizationId}:{$date}");
    }
}
