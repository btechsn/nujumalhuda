<?php

namespace Modules\Education\Filament\Resources\ProgramTypeResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Modules\Education\Filament\Resources\ProgramTypeResource;

class ListProgramTypes extends ListRecords
{
    protected static string $resource = ProgramTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
