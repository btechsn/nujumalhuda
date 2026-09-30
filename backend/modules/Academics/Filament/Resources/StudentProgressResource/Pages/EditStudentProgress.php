<?php

namespace Modules\Academics\Filament\Resources\StudentProgressResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Modules\Academics\Filament\Resources\StudentProgressResource;

class EditStudentProgress extends EditRecord
{
    protected static string $resource = StudentProgressResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
