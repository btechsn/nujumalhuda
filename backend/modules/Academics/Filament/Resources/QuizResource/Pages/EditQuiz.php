<?php

namespace Modules\Academics\Filament\Resources\QuizResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Modules\Academics\Filament\Resources\QuizResource;

class EditQuiz extends EditRecord
{
    protected static string $resource = QuizResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
