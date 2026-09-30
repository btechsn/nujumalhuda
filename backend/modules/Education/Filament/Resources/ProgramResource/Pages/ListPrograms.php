<?php

namespace Modules\Education\Filament\Resources\ProgramResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Modules\Education\Filament\Resources\ProgramResource;

class ListPrograms extends ListRecords
{
    protected static string $resource = ProgramResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
