<?php

namespace Modules\Community\Filament\Resources\QuestionResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\Community\Filament\Resources\QuestionResource;

class CreateQuestion extends CreateRecord
{
    protected static string $resource = QuestionResource::class;

    public function getTitle(): string
    {
        return 'Nouvelle question';
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['asker_id'] = auth()->id();

        $answer = trim((string) ($data['answer_i18n']['fr'] ?? ''));
        if ($answer !== '' && blank($data['answered_at'] ?? null)) {
            $data['answered_at'] = now();
        }

        return $data;
    }
}
