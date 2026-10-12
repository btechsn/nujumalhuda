<?php

namespace Modules\Resources\Services;

use Modules\Core\Support\EditorialText;
use Modules\Resources\Models\DailyContent;

class DailyContentService
{
    public function forToday(): array
    {
        $today = now()->toDateString();

        return [
            'verse' => $this->resolve('verse', $today),
            'hadith' => $this->resolve('hadith', $today),
        ];
    }

    protected function resolve(string $type, string $today): ?array
    {
        $exact = DailyContent::query()
            ->where('type', $type)
            ->where('is_published', true)
            ->whereDate('display_date', $today)
            ->first();

        if ($exact) {
            return $this->payload($exact, false);
        }

        $fallback = DailyContent::query()
            ->where('type', $type)
            ->where('is_published', true)
            ->whereDate('display_date', '<=', $today)
            ->orderByDesc('display_date')
            ->first();

        return $fallback ? $this->payload($fallback, true) : null;
    }

    protected function payload(DailyContent $content, bool $fallback): array
    {
        $locale = app()->getLocale();
        $translation = EditorialText::present($content->translation_i18n, $locale);
        $commentary = EditorialText::present($content->commentary_i18n, $locale);

        return [
            'id' => $content->id,
            'type' => $content->type,
            'display_date' => $content->display_date->toDateString(),
            'arabic_text' => $content->arabic_text,
            'translation' => $translation['text'],
            'translation_fell_back' => $translation['fell_back'],
            'commentary' => $commentary['text'] !== '' ? $commentary['text'] : null,
            'commentary_fell_back' => $commentary['fell_back'],
            'source' => $content->source,
            'reference' => $content->reference,
            'is_fallback' => $fallback,
        ];
    }
}
