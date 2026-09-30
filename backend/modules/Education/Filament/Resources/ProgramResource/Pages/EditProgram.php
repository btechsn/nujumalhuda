<?php

namespace Modules\Education\Filament\Resources\ProgramResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Modules\Education\Filament\Resources\ProgramResource;

class EditProgram extends EditRecord
{
    protected static string $resource = ProgramResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
