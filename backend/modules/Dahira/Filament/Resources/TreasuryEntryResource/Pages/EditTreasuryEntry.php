<?php

namespace Modules\Dahira\Filament\Resources\TreasuryEntryResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Modules\Dahira\Filament\Resources\TreasuryEntryResource;

class EditTreasuryEntry extends EditRecord
{
    protected static string $resource = TreasuryEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
