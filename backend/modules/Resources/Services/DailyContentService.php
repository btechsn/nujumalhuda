<?php

namespace Modules\Resources\Services;

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

        return [
            'id' => $content->id,
            'type' => $content->type,
            'display_date' => $content->display_date->toDateString(),
            'arabic_text' => $content->arabic_text,
            'translation' => $content->translation_i18n[$locale] ?? $content->translation_i18n['fr'] ?? '',
            'commentary' => $content->commentary_i18n[$locale] ?? $content->commentary_i18n['fr'] ?? null,
            'source' => $content->source,
            'reference' => $content->reference,
            'is_fallback' => $fallback,
        ];
    }
}
