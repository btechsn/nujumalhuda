<?php

namespace Modules\Education\Filament\Resources\ProgramTypeResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Modules\Education\Filament\Resources\ProgramTypeResource;

class EditProgramType extends EditRecord
{
    protected static string $resource = ProgramTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
