<?php

namespace Modules\Dahira\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Models\Membership;

class ContributionSchedule extends Model
{
    use HasUlids;

    protected $fillable = [
        'plan_id', 'membership_id', 'period_start', 'due_on',
        'amount_minor', 'status', 'reminded_at',
    ];

    protected $casts = [
        'period_start' => 'date',
        'due_on' => 'date',
        'amount_minor' => 'integer',
        'reminded_at' => 'datetime',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(ContributionPlan::class, 'plan_id');
    }

    public function membership(): BelongsTo
    {
        return $this->belongsTo(Membership::class);
    }
}
