<?php

namespace Modules\Dahira\Filament\Resources\TreasuryEntryResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\Dahira\Filament\Resources\TreasuryEntryResource;

class CreateTreasuryEntry extends CreateRecord
{
    protected static string $resource = TreasuryEntryResource::class;

    public function getTitle(): string
    {
        return 'Nouvelle écriture';
    }
}
