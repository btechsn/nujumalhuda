<?php

namespace Modules\Dahira\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Models\Membership;
use Modules\Core\Models\User;

class DahiraJoinRequest extends Model
{
    use HasUlids;

    protected $fillable = [
        'dahira_group_id',
        'first_name',
        'last_name',
        'phone',
        'message',
        'status',
        'reviewed_by',
        'reviewed_at',
        'membership_id',
        'review_note',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(DahiraGroup::class, 'dahira_group_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function membership(): BelongsTo
    {
        return $this->belongsTo(Membership::class);
    }

    public function fullName(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }
}
