<?php

namespace Modules\Dahira\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Core\Models\Organization;

class DahiraGroup extends Model
{
    use HasUlids;

    protected $fillable = [
        'organization_id', 'name_i18n', 'description_i18n', 'founded_on',
        'meeting_weekday', 'meeting_time', 'location', 'is_active',
    ];

    protected $casts = [
        'name_i18n' => 'array',
        'description_i18n' => 'array',
        'founded_on' => 'date',
        'is_active' => 'boolean',
        'meeting_weekday' => 'integer',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function plans(): HasMany
    {
        return $this->hasMany(ContributionPlan::class);
    }

    public function meetings(): HasMany
    {
        return $this->hasMany(Meeting::class);
    }

    public function treasuryEntries(): HasMany
    {
        return $this->hasMany(TreasuryEntry::class);
    }

    public function joinRequests(): HasMany
    {
        return $this->hasMany(DahiraJoinRequest::class);
    }

    public function balanceMinor(): int
    {
        $in = (int) $this->treasuryEntries()->where('direction', 'in')->sum('amount_minor');
        $out = (int) $this->treasuryEntries()->where('direction', 'out')->sum('amount_minor');

        return $in - $out;
    }
}
