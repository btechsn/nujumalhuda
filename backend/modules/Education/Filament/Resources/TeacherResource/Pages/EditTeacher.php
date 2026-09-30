<?php

namespace Modules\Education\Filament\Resources\TeacherResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Modules\Education\Filament\Resources\TeacherResource;

class EditTeacher extends EditRecord
{
    protected static string $resource = TeacherResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
