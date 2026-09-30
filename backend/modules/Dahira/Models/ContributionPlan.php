<?php

namespace Modules\Dahira\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ContributionPlan extends Model
{
    use HasUlids;

    protected $fillable = [
        'dahira_group_id', 'name_i18n', 'amount_minor', 'currency',
        'frequency', 'due_day', 'is_active',
    ];

    protected $casts = [
        'name_i18n' => 'array',
        'amount_minor' => 'integer',
        'due_day' => 'integer',
        'is_active' => 'boolean',
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(DahiraGroup::class, 'dahira_group_id');
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(ContributionSchedule::class, 'plan_id');
    }
}
