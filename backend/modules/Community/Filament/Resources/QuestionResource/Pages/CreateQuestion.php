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

        return QuestionResource::enforceAttribution($data);
    }
}
