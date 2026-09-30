<?php

namespace Modules\Community\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Models\User;

class Sponsorship extends Model
{
    use HasUlids;

    protected $fillable = [
        'sponsor_id', 'student_id', 'amount_minor', 'currency', 'frequency', 'status',
    ];

    protected $casts = ['amount_minor' => 'integer'];

    public function sponsor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sponsor_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}
