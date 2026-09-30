<?php

namespace Modules\News\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArticleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title_i18n,
            'excerpt' => $this->excerpt_i18n,
            'content' => $this->when($request->routeIs('news.articles.show'), $this->content_i18n),
            'slug' => $this->slug,
            'author' => [
                'id' => $this->author->id,
                'name' => $this->author->name,
            ],
            'category' => $this->when($this->category, [
                'id' => $this->category?->id,
                'name' => $this->category?->name_i18n,
                'slug' => $this->category?->slug,
            ]),
            'cover_image_url' => $this->coverImage?->url(),
            'tags' => $this->tags,
            'views_count' => $this->views_count,
            'comments_count' => $this->comments_count,
            'is_featured' => $this->is_featured,
            'allow_comments' => $this->allow_comments,
            'published_at' => $this->published_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'comments' => $this->when($request->routeIs('news.articles.show'),
                CommentResource::collection($this->approvedComments)
            ),
        ];
    }
}
