<?php

namespace Modules\Academics\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Models\User;

class RecitationEvaluation extends Model
{
    use HasUlids;

    protected $fillable = [
        'student_id', 'teacher_id', 'milestone_id', 'live_stream_id',
        'memorization', 'tajwid', 'fluency', 'comments', 'evaluated_at',
    ];

    protected $casts = [
        'memorization' => 'integer',
        'tajwid' => 'integer',
        'fluency' => 'integer',
        'evaluated_at' => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function milestone(): BelongsTo
    {
        return $this->belongsTo(HifzMilestone::class, 'milestone_id');
    }
}
