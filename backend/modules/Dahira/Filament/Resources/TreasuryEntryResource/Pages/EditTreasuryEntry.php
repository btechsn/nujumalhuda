<?php

namespace Modules\Dahira\Filament\Resources\TreasuryEntryResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Modules\Dahira\Filament\Resources\TreasuryEntryResource;

class EditTreasuryEntry extends EditRecord
{
    protected static string $resource = TreasuryEntryResource::class;

    public function getTitle(): string
    {
        return 'Modifier l’écriture';
    }

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
