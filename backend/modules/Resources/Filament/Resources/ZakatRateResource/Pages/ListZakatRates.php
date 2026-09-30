<?php

namespace Modules\Resources\Filament\Resources\ZakatRateResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Modules\Resources\Filament\Resources\ZakatRateResource;

class ListZakatRates extends ListRecords
{
    protected static string $resource = ZakatRateResource::class;

    protected function getHeaderActions(): array
    {
        return [\Filament\Actions\CreateAction::make()];
    }
}
