<?php

namespace Modules\News\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Modules\News\Models\Article;

class RssFeedController extends Controller
{
    public function __invoke(): Response
    {
        $articles = Article::query()
            ->with(['author', 'category'])
            ->published()
            ->orderByDesc('published_at')
            ->limit(30)
            ->get();

        $frontend = rtrim((string) config('app.frontend_url', config('app.url')), '/');
        $feedUrl = url('/api/v1/news/feed');

        $items = $articles->map(function (Article $article) use ($frontend) {
            $title = $this->escape($article->getTitle('fr') ?? 'Article');
            $excerpt = $article->excerpt_i18n['fr']
                ?? mb_strimwidth(strip_tags((string) ($article->content_i18n['fr'] ?? '')), 0, 280, '…');
            $link = $frontend . '/fr/news/' . $article->slug;
            $pubDate = $article->published_at?->toRssString() ?? now()->toRssString();
            $author = $this->escape($article->author?->name ?? 'Nujum Al-Huda');
            $category = $this->escape($article->category?->name_i18n['fr'] ?? '');

            return <<<XML
        <item>
            <title>{$title}</title>
            <link>{$link}</link>
            <guid isPermaLink="true">{$link}</guid>
            <pubDate>{$pubDate}</pubDate>
            <author>{$author}</author>
            <category>{$category}</category>
            <description>{$this->escape($excerpt)}</description>
        </item>
XML;
        })->implode("\n");

        $builtAt = $this->lastBuildDate($articles);
        $selfLink = $this->escape($feedUrl);

        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
    <channel>
        <title>Nujum Al-Huda — Actualités</title>
        <link>{$frontend}/fr/news</link>
        <description>Actualités, enseignements et vie de l'institut Nujum Al-Huda.</description>
        <language>fr</language>
        <lastBuildDate>{$builtAt}</lastBuildDate>
        <atom:link href="{$selfLink}" rel="self" type="application/rss+xml"/>
        {$items}
    </channel>
</rss>
XML;

        return response($xml, 200, [
            'Content-Type' => 'application/rss+xml; charset=UTF-8',
            'Cache-Control' => 'public, max-age=900',
        ]);
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    private function lastBuildDate($articles): string
    {
        $latest = $articles->first()?->published_at;

        return ($latest ?? now())->toRssString();
    }
}
