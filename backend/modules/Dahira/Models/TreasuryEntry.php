<?php

namespace Modules\Dahira\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Models\User;

class TreasuryEntry extends Model
{
    use HasUlids;

    protected $fillable = [
        'dahira_group_id', 'direction', 'category', 'amount_minor', 'currency',
        'label', 'occurred_on', 'contribution_id', 'payment_id', 'recorded_by', 'note',
    ];

    protected $casts = [
        'amount_minor' => 'integer',
        'occurred_on' => 'date',
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(DahiraGroup::class, 'dahira_group_id');
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
