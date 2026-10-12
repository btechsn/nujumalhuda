<?php

declare(strict_types=1);

namespace Modules\Education\Listeners;

use Modules\Core\Events\PublicSearchRunning;
use Modules\Core\Support\EditorialText;
use Modules\Education\Models\Program;

class SearchActivePrograms
{
    public function handle(PublicSearchRunning $event): void
    {
        $like = '%'.str_replace(['%', '_'], '', $event->query).'%';
        $programs = Program::query()
            ->where('is_active', true)
            ->where(function ($query) use ($like) {
                $query->where('name_i18n->fr', 'ilike', $like)
                    ->orWhere('name_i18n->en', 'ilike', $like)
                    ->orWhere('name_i18n->ar', 'ilike', $like);
            })
            ->orderBy('display_order')
            ->limit(8)
            ->get();

        foreach ($programs as $program) {
            $title = EditorialText::present($program->name_i18n, $event->locale);
            if ($title['text'] === '') {
                continue;
            }
            $event->results[] = [
                'kind' => 'program',
                'title' => $title['text'],
                'url' => '/programs/'.$program->id,
                'fell_back' => $title['fell_back'],
            ];
        }
    }
}
