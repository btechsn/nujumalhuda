<?php

namespace Modules\Academics\Filament\Resources\QuizTopicResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;
use Modules\Academics\Filament\Resources\QuizTopicResource;
use Modules\Academics\Models\QuizTopic;

class CreateQuizTopic extends CreateRecord
{
    protected static string $resource = QuizTopicResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $name = is_array($data['name_i18n'] ?? null) ? ($data['name_i18n']['fr'] ?? '') : '';
        $base = Str::slug((string) ($data['slug'] ?? $name)) ?: 'theme';
        $slug = $base;
        $suffix = 2;

        while (QuizTopic::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        $data['slug'] = $slug;

        return $data;
    }
}
