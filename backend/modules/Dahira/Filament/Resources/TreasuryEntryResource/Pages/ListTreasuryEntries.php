<?php

namespace Modules\Dahira\Filament\Resources\TreasuryEntryResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Modules\Dahira\Filament\Resources\TreasuryEntryResource;

class ListTreasuryEntries extends ListRecords
{
    protected static string $resource = TreasuryEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
