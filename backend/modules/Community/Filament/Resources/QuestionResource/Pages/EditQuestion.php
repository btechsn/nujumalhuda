<?php

namespace Modules\Community\Filament\Resources\QuestionResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Modules\Community\Filament\Resources\QuestionResource;

class EditQuestion extends EditRecord
{
    protected static string $resource = QuestionResource::class;

    public function getTitle(): string
    {
        return 'Modifier la question';
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $answer = trim((string) ($data['answer_i18n']['fr'] ?? ''));
        if ($answer !== '' && blank($data['answered_at'] ?? null)) {
            $data['answered_at'] = now();
        }

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
