<?php

namespace Modules\Academics\Filament\Resources\IjazaResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Modules\Academics\Filament\Resources\IjazaResource;

class ListIjazas extends ListRecords
{
    protected static string $resource = IjazaResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
