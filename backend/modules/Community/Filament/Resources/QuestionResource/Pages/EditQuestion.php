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
        return QuestionResource::enforceAttribution($data);
    }

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
