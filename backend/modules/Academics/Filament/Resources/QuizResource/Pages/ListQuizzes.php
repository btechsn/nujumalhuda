<?php

namespace Modules\Academics\Filament\Resources\QuizResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Modules\Academics\Filament\Resources\QuizResource;

class ListQuizzes extends ListRecords
{
    protected static string $resource = QuizResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
