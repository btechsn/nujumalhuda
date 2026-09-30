<?php

namespace Modules\Resources\Filament\Resources\DailyContentResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Modules\Resources\Filament\Resources\DailyContentResource;

class ListDailyContents extends ListRecords
{
    protected static string $resource = DailyContentResource::class;

    protected function getHeaderActions(): array
    {
        return [\Filament\Actions\CreateAction::make()];
    }
}
