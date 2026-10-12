<?php

declare(strict_types=1);

namespace Modules\Mosque\Listeners;

use Modules\Core\Events\PublicSearchRunning;
use Modules\Core\Support\EditorialText;
use Modules\Mosque\Models\Khutba;

class SearchPublishedKhutbas
{
    public function handle(PublicSearchRunning $event): void
    {
        $like = '%'.str_replace(['%', '_'], '', $event->query).'%';
        $khutbas = Khutba::query()
            ->published()
            ->where(function ($query) use ($like) {
                $query->where('title_i18n->fr', 'ilike', $like)
                    ->orWhere('title_i18n->en', 'ilike', $like)
                    ->orWhere('title_i18n->ar', 'ilike', $like);
            })
            ->orderByDesc('date')
            ->limit(8)
            ->get();

        foreach ($khutbas as $khutba) {
            $title = EditorialText::present($khutba->title_i18n, $event->locale);
            if ($title['text'] === '') {
                continue;
            }
            $event->results[] = [
                'kind' => 'khutba',
                'title' => $title['text'],
                'url' => '/centre/khutbas/'.$khutba->id,
                'fell_back' => $title['fell_back'],
            ];
        }
    }
}
