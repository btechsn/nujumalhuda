<?php

namespace Modules\Community\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Models\User;

class Donation extends Model
{
    use HasUlids;

    protected $fillable = [
        'user_id', 'donor_name', 'is_anonymous', 'amount_minor', 'currency',
        'status', 'message', 'paid_at', 'payment_id',
    ];

    protected $casts = [
        'is_anonymous' => 'boolean',
        'amount_minor' => 'integer',
        'paid_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function publicName(): string
    {
        return $this->is_anonymous ? 'Donateur anonyme' : ($this->donor_name ?: 'Donateur');
    }
}
