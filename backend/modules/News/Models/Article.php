<?php

namespace Modules\News\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Models\Media;
use Modules\Core\Models\Organization;
use Modules\Core\Models\User;

class Article extends Model
{
    use HasFactory, HasUlids, SoftDeletes;

    protected $fillable = [
        'author_id',
        'category_id',
        'organization_id',
        'title_i18n',
        'excerpt_i18n',
        'content_i18n',
        'slug',
        'meta_description_i18n',
        'meta_keywords',
        'cover_image_id',
        'status',
        'published_at',
        'views_count',
        'comments_count',
        'tags',
        'is_featured',
        'allow_comments',
    ];

    protected $casts = [
        'title_i18n' => 'array',
        'excerpt_i18n' => 'array',
        'content_i18n' => 'array',
        'meta_description_i18n' => 'array',
        'meta_keywords' => 'array',
        'tags' => 'array',
        'published_at' => 'datetime',
        'is_featured' => 'boolean',
        'allow_comments' => 'boolean',
        'views_count' => 'integer',
        'comments_count' => 'integer',
    ];

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ArticleCategory::class, 'category_id');
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function coverImage(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'cover_image_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(ArticleComment::class, 'article_id');
    }

    public function approvedComments(): HasMany
    {
        return $this->comments()->where('status', 'approved');
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published')
            ->where('published_at', '<=', now());
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where(function ($builder) use ($category) {
            $builder->where('category_id', $category)
                ->orWhereHas('category', fn ($q) => $q->where('slug', $category));
        });
    }

    public function getTitle(string $locale = 'fr'): ?string
    {
        return $this->title_i18n[$locale] ?? $this->title_i18n['fr'] ?? null;
    }

    public function incrementViews(): void
    {
        $this->increment('views_count');
    }

    public function updateCommentsCount(): void
    {
        $this->comments_count = $this->approvedComments()->count();
        $this->saveQuietly();
    }
}
