<?php

declare(strict_types=1);

namespace Modules\News\Listeners;

use Modules\Core\Events\PublicSearchRunning;
use Modules\Core\Support\EditorialText;
use Modules\News\Models\Article;

class SearchPublishedArticles
{
    public function handle(PublicSearchRunning $event): void
    {
        $like = $this->like($event->query);
        $articles = Article::query()
            ->published()
            ->where(function ($query) use ($like) {
                $query->where('title_i18n->fr', 'ilike', $like)
                    ->orWhere('title_i18n->en', 'ilike', $like)
                    ->orWhere('title_i18n->ar', 'ilike', $like);
            })
            ->orderByDesc('published_at')
            ->limit(8)
            ->get();

        foreach ($articles as $article) {
            $title = EditorialText::present($article->title_i18n, $event->locale);
            if ($title['text'] === '') {
                continue;
            }
            $event->results[] = [
                'kind' => 'article',
                'title' => $title['text'],
                'url' => '/actualites/'.$article->slug,
                'fell_back' => $title['fell_back'],
            ];
        }
    }

    private function like(string $query): string
    {
        return '%'.str_replace(['%', '_'], '', $query).'%';
    }
}
