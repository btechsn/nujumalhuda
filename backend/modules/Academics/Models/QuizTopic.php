<?php

namespace Modules\Academics\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuizTopic extends Model
{
    use HasUlids;

    protected $fillable = [
        'slug',
        'name_i18n',
        'sort',
    ];

    protected $casts = [
        'name_i18n' => 'array',
        'sort' => 'integer',
    ];

    public function quizzes(): HasMany
    {
        return $this->hasMany(Quiz::class, 'topic', 'slug');
    }

    public function label(string $locale = 'fr'): string
    {
        $name = $this->name_i18n ?? [];

        return (string) ($name[$locale] ?? $name['fr'] ?? $this->slug);
    }
}
