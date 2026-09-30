<?php

namespace Modules\Mosque\Filament\Resources\KhutbaResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Modules\Mosque\Filament\Resources\KhutbaResource;

class EditKhutba extends EditRecord
{
    protected static string $resource = KhutbaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
