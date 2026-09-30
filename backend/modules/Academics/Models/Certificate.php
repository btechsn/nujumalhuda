<?php

namespace Modules\Academics\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Models\User;

class Certificate extends Model
{
    use HasUlids;

    protected $fillable = [
        'student_id', 'milestone_id', 'progress_id', 'verification_code',
        'level_label_i18n', 'issued_at', 'document_path',
    ];

    protected $casts = [
        'level_label_i18n' => 'array',
        'issued_at' => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function milestone(): BelongsTo
    {
        return $this->belongsTo(HifzMilestone::class, 'milestone_id');
    }

    public function progress(): BelongsTo
    {
        return $this->belongsTo(StudentProgress::class, 'progress_id');
    }
}
