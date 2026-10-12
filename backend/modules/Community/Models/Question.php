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

    /**
     * Cahier des charges §8.4 : une réponse publique porte le nom d'un
     * enseignant. Sans nom, elle n'est pas publiable.
     */
    public function teacherName(): ?string
    {
        $name = trim((string) $this->teacher?->fullName());

        return $name !== '' ? $name : null;
    }

    public function publicAnswer(string $locale): ?string
    {
        if ($this->teacherName() === null) {
            return null;
        }

        $text = trim((string) ($this->answer_i18n[$locale] ?? $this->answer_i18n['fr'] ?? ''));

        return $text !== '' ? $text : null;
    }
}
