<?php

namespace Modules\News\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ArticleCategory extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'name_i18n',
        'description_i18n',
        'slug',
        'display_order',
        'is_active',
    ];

    protected $casts = [
        'name_i18n' => 'array',
        'description_i18n' => 'array',
        'is_active' => 'boolean',
        'display_order' => 'integer',
    ];

    public function articles(): HasMany
    {
        return $this->hasMany(Article::class, 'category_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order')->orderBy('name_i18n->fr');
    }

    public function getName(string $locale = 'fr'): ?string
    {
        return $this->name_i18n[$locale] ?? $this->name_i18n['fr'] ?? null;
    }
}
