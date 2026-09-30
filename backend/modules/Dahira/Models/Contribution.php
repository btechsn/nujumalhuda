<?php

namespace Modules\Dahira\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Modules\Core\Models\Membership;
use Modules\Core\Models\Payment;
use Modules\Core\Models\User;

class Contribution extends Model
{
    use HasUlids;

    protected $fillable = [
        'dahira_group_id', 'membership_id', 'schedule_id', 'payment_id',
        'recorded_by', 'amount_minor', 'currency', 'paid_on', 'note',
    ];

    protected $casts = [
        'amount_minor' => 'integer',
        'paid_on' => 'date',
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(DahiraGroup::class, 'dahira_group_id');
    }

    public function membership(): BelongsTo
    {
        return $this->belongsTo(Membership::class);
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(ContributionSchedule::class, 'schedule_id');
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function payablePayment(): MorphOne
    {
        return $this->morphOne(Payment::class, 'payable');
    }
}
