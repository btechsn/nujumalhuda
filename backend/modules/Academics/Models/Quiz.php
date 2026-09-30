<?php

namespace Modules\Academics\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quiz extends Model
{
    use HasUlids;

    protected $fillable = ['slug', 'topic', 'title_i18n', 'questions', 'is_published'];

    protected $casts = [
        'title_i18n' => 'array',
        'questions' => 'array',
        'is_published' => 'boolean',
    ];

    public function attempts(): HasMany
    {
        return $this->hasMany(QuizAttempt::class);
    }

    public function publicQuestions(): array
    {
        return collect($this->questions ?? [])->map(function (array $question) {
            unset($question['correct_index']);

            return $question;
        })->all();
    }
}
