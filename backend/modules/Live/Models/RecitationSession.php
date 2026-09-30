<?php

namespace Modules\Live\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

class RecitationSession extends Model
{
    use HasUlids;

    protected $fillable = [
        'trigger', 'live_stream_id', 'student_id', 'teacher_id',
        'milestone_id', 'title', 'starts_at', 'status',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
    ];
}
