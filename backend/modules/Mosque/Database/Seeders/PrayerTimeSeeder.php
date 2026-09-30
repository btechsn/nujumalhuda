<?php

namespace Modules\Mosque\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Modules\Core\Models\Organization;
use Modules\Mosque\Models\PrayerTime;
use Modules\Mosque\Models\IqamaAdjustment;

class PrayerTimeSeeder extends Seeder
{
    public function run(): void
    {
        $organizationId = Organization::query()->where('slug', 'nujum-al-huda')->value('id');

        // Créer des décalages iqama par défaut
        $prayers = ['fajr', 'dhuhr', 'asr', 'maghrib', 'isha'];
        $iqamaOffsets = [
            'fajr' => 20,
            'dhuhr' => 15,
            'asr' => 15,
            'maghrib' => 5,
            'isha' => 15,
        ];

        foreach ($prayers as $prayer) {
            IqamaAdjustment::create([
                'id' => Str::ulid(),
                'organization_id' => $organizationId,
                'prayer_name' => $prayer,
                'minutes_offset' => $iqamaOffsets[$prayer],
                'valid_from' => now()->subMonths(3),
                'valid_to' => null, // Indéfini
                'description' => 'Décalage standard',
                'is_active' => true,
            ]);
        }

        // Horaires de prière pour les 7 prochains jours (horaires réalistes pour Dakar)
        $prayerSchedule = [
            'fajr' => '05:30',
            'dhuhr' => '13:15',
            'asr' => '16:45',
            'maghrib' => '19:10',
            'isha' => '20:25',
        ];

        for ($day = 0; $day < 7; $day++) {
            $date = now()->addDays($day);

            foreach ($prayers as $prayer) {
                $calculatedTime = $prayerSchedule[$prayer];
                $iqamaOffset = $iqamaOffsets[$prayer];
                $iqamaTime = \Carbon\Carbon::parse($calculatedTime)->addMinutes($iqamaOffset)->format('H:i');

                PrayerTime::create([
                    'id' => Str::ulid(),
                    'organization_id' => $organizationId,
                    'date' => $date,
                    'prayer_name' => $prayer,
                    'calculated_time' => $calculatedTime,
                    'calculation_method' => 'MWL',
                    'manual_time' => null,
                    'is_overridden' => false,
                    'iqama_time' => $iqamaTime,
                    'metadata' => [
                        'latitude' => 14.6928,
                        'longitude' => -17.4467,
                        'timezone' => 'Africa/Dakar',
                    ],
                ]);
            }
        }

        $this->command->info('✅ Horaires de prière créés pour 7 jours + décalages iqama !');
    }
}
