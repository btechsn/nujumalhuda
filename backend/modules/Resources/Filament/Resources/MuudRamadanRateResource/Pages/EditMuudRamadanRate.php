<?php

namespace Modules\Resources\Filament\Resources\MuudRamadanRateResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Modules\Resources\Filament\Resources\MuudRamadanRateResource;

class EditMuudRamadanRate extends EditRecord
{
    protected static string $resource = MuudRamadanRateResource::class;

    public function getTitle(): string
    {
        return 'Modifier le barème';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
