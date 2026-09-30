<?php

namespace Modules\Mosque\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Models\Organization;
use Modules\Core\Models\User;

class PrayerTime extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'organization_id',
        'date',
        'prayer_name',
        'calculated_time',
        'calculation_method',
        'manual_time',
        'is_overridden',
        'overridden_by',
        'overridden_at',
        'override_reason',
        'iqama_time',
        'metadata',
    ];

    protected $casts = [
        'date' => 'date',
        'calculated_time' => 'datetime:H:i',
        'manual_time' => 'datetime:H:i',
        'iqama_time' => 'datetime:H:i',
        'is_overridden' => 'boolean',
        'overridden_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function overriddenBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'overridden_by');
    }

    /**
     * Obtenir l'heure effective (manual prioritaire sur calculated)
     */
    public function getEffectiveTime(): ?string
    {
        return $this->manual_time ?? $this->calculated_time;
    }

    /**
     * Override manuel de l'heure
     */
    public function override(string $time, User $user, ?string $reason = null): void
    {
        $this->update([
            'manual_time' => $time,
            'is_overridden' => true,
            'overridden_by' => $user->id,
            'overridden_at' => now(),
            'override_reason' => $reason,
        ]);
    }

    /**
     * Supprimer l'override et revenir au calcul automatique
     */
    public function removeOverride(): void
    {
        $this->update([
            'manual_time' => null,
            'is_overridden' => false,
            'overridden_by' => null,
            'overridden_at' => null,
            'override_reason' => null,
        ]);
    }
}
