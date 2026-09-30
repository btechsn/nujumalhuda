<?php

namespace Modules\Academics\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Modules\Core\Models\User;

class StudentProgress extends Model
{
    use HasUlids;

    protected $table = 'student_progress';

    protected $fillable = [
        'student_id', 'milestone_id', 'status', 'started_at', 'completed_at', 'teacher_note',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function milestone(): BelongsTo
    {
        return $this->belongsTo(HifzMilestone::class, 'milestone_id');
    }

    public function certificate(): HasOne
    {
        return $this->hasOne(Certificate::class, 'progress_id');
    }
}
