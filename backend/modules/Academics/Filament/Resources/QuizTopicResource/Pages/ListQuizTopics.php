<?php

namespace Modules\Academics\Filament\Resources\QuizTopicResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Modules\Academics\Filament\Resources\QuizTopicResource;

class ListQuizTopics extends ListRecords
{
    protected static string $resource = QuizTopicResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
