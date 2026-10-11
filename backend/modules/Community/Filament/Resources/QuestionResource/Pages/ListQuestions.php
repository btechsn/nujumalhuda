<?php

namespace Modules\Community\Filament\Resources\QuestionResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Modules\Community\Filament\Resources\QuestionResource;

class ListQuestions extends ListRecords
{
    protected static string $resource = QuestionResource::class;

    public function getTitle(): string
    {
        return 'FAQ';
    }

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
