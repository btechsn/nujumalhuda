<?php

namespace Modules\Community\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Models\User;

class Question extends Model
{
    use HasUlids;

    protected $fillable = [
        'asker_id', 'teacher_id', 'question_i18n', 'answer_i18n',
        'topics', 'status', 'is_public', 'answered_at',
    ];

    protected $casts = [
        'question_i18n' => 'array',
        'answer_i18n' => 'array',
        'topics' => 'array',
        'is_public' => 'boolean',
        'answered_at' => 'datetime',
    ];

    public function asker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'asker_id');
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }
}
