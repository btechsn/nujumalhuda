<?php

namespace Modules\Mosque\Filament\Resources\HijriObservanceResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Modules\Mosque\Filament\Resources\HijriObservanceResource;

class ListHijriObservances extends ListRecords
{
    protected static string $resource = HijriObservanceResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
