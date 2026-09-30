<?php

namespace Modules\Dahira\Filament\Resources\DahiraGroupResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Modules\Dahira\Filament\Resources\DahiraGroupResource;

class ListDahiraGroups extends ListRecords
{
    protected static string $resource = DahiraGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
