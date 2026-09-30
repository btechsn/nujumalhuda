<?php

namespace Modules\Mosque\Observers;

use Modules\Mosque\Models\PrayerTime;
use Modules\Mosque\Models\PrayerTimeHistory;

class PrayerTimeObserver
{
    /**
     * Handle the PrayerTime "updating" event.
     */
    public function updating(PrayerTime $prayerTime): void
    {
        // Récupérer les valeurs originales avant la mise à jour
        $original = $prayerTime->getOriginal();

        // Vérifier si des champs importants ont changé
        $hasChanges = $prayerTime->isDirty([
            'calculated_time',
            'manual_time',
            'is_overridden',
            'iqama_time',
        ]);

        if (!$hasChanges) {
            return;
        }

        // Déterminer le type de modification
        $changeType = 'recalculation';
        
        if ($prayerTime->isDirty('manual_time') || $prayerTime->isDirty('is_overridden')) {
            if ($prayerTime->is_overridden && !$original['is_overridden']) {
                $changeType = 'manual_override';
            } elseif (!$prayerTime->is_overridden && $original['is_overridden']) {
                $changeType = 'remove_override';
            }
        } elseif ($prayerTime->isDirty('iqama_time')) {
            $changeType = 'iqama_adjustment';
        }

        // Créer l'entrée d'historique
        PrayerTimeHistory::create([
            'prayer_time_id' => $prayerTime->id,
            'changed_by' => auth()->id(),
            
            // Valeurs avant
            'old_calculated_time' => $original['calculated_time'],
            'old_manual_time' => $original['manual_time'],
            'old_is_overridden' => $original['is_overridden'],
            'old_iqama_time' => $original['iqama_time'],
            
            // Valeurs après
            'new_calculated_time' => $prayerTime->calculated_time,
            'new_manual_time' => $prayerTime->manual_time,
            'new_is_overridden' => $prayerTime->is_overridden,
            'new_iqama_time' => $prayerTime->iqama_time,
            
            'change_type' => $changeType,
            'reason' => $prayerTime->override_reason ?? null,
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
            'changed_at' => now(),
        ]);
    }
}
