<?php

namespace Modules\Academics\Filament\Resources\QuizTopicResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Modules\Academics\Filament\Resources\QuizTopicResource;

class EditQuizTopic extends EditRecord
{
    protected static string $resource = QuizTopicResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
