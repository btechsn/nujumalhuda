<?php

namespace Modules\Resources\Filament\Resources\ZakatRateResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Modules\Resources\Filament\Resources\ZakatRateResource;

class EditZakatRate extends EditRecord
{
    protected static string $resource = ZakatRateResource::class;

    protected function getHeaderActions(): array
    {
        return [\Filament\Actions\DeleteAction::make()];
    }
}
