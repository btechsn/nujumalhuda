<?php

declare(strict_types=1);

namespace Modules\News\Listeners;

use Modules\Core\Events\AssistantKnowledgeCollecting;
use Modules\Core\Support\EditorialText;
use Modules\News\Models\Article;
use Throwable;

class CollectNewsAssistantKnowledge
{
    public function handle(AssistantKnowledgeCollecting $event): void
    {
        try {
            $articles = Article::query()
                ->published()
                ->orderByDesc('published_at')
                ->limit(12)
                ->get();

            foreach ($articles as $article) {
                $title = EditorialText::present($article->title_i18n, $event->locale)['text'];
                if ($title === '') {
                    continue;
                }
                $excerpt = EditorialText::present($article->excerpt_i18n, $event->locale)['text'];
                $event->add(
                    'article.'.$article->id,
                    $title,
                    $excerpt,
                    '/'.$event->locale.'/actualites/'.$article->slug,
                );
            }
        } catch (Throwable) {
            // Une base indisponible n'empêche pas les autres fiches.
        }
    }
}
