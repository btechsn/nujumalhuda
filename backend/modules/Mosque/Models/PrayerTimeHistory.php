<?php

namespace Modules\Mosque\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Models\User;

class PrayerTimeHistory extends Model
{
    use HasFactory, HasUlids;

    protected $table = 'prayer_time_history';

    public $timestamps = false; // On utilise changed_at

    protected $fillable = [
        'prayer_time_id',
        'changed_by',
        'old_calculated_time',
        'old_manual_time',
        'old_is_overridden',
        'old_iqama_time',
        'new_calculated_time',
        'new_manual_time',
        'new_is_overridden',
        'new_iqama_time',
        'change_type',
        'reason',
        'ip_address',
        'user_agent',
        'metadata',
        'changed_at',
    ];

    protected $casts = [
        'old_is_overridden' => 'boolean',
        'new_is_overridden' => 'boolean',
        'metadata' => 'array',
        'changed_at' => 'datetime',
    ];

    /**
     * Horaire de prière
     */
    public function prayerTime(): BelongsTo
    {
        return $this->belongsTo(PrayerTime::class);
    }

    /**
     * Utilisateur qui a effectué la modification
     */
    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    /**
     * Scopes
     */
    public function scopeManualOverrides($query)
    {
        return $query->where('change_type', 'manual_override');
    }

    public function scopeRemoveOverrides($query)
    {
        return $query->where('change_type', 'remove_override');
    }

    public function scopeIqamaAdjustments($query)
    {
        return $query->where('change_type', 'iqama_adjustment');
    }

    public function scopeRecalculations($query)
    {
        return $query->where('change_type', 'recalculation');
    }

    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('changed_at', '>=', now()->subDays($days));
    }

    /**
     * Helpers
     */
    public function wasManuallyOverridden(): bool
    {
        return $this->change_type === 'manual_override';
    }

    public function wasOverrideRemoved(): bool
    {
        return $this->change_type === 'remove_override';
    }

    public function wasIqamaAdjusted(): bool
    {
        return $this->change_type === 'iqama_adjustment';
    }

    public function wasRecalculated(): bool
    {
        return $this->change_type === 'recalculation';
    }

    /**
     * Obtenir un résumé lisible de la modification
     */
    public function getSummary(): string
    {
        return match ($this->change_type) {
            'manual_override' => "Horaire modifié manuellement de {$this->old_calculated_time} à {$this->new_manual_time}",
            'remove_override' => "Override retiré, retour à l'horaire calculé {$this->new_calculated_time}",
            'iqama_adjustment' => "Iqama ajusté de {$this->old_iqama_time} à {$this->new_iqama_time}",
            'recalculation' => "Horaire recalculé de {$this->old_calculated_time} à {$this->new_calculated_time}",
            default => "Modification inconnue",
        };
    }
}
