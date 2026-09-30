<?php

namespace Modules\Resources\Filament\Resources\MuudRamadanRateResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Modules\Resources\Filament\Resources\MuudRamadanRateResource;

class ListMuudRamadanRates extends ListRecords
{
    protected static string $resource = MuudRamadanRateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
