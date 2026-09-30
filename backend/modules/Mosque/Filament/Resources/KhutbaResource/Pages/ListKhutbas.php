<?php

namespace Modules\Mosque\Filament\Resources\KhutbaResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Modules\Mosque\Filament\Resources\KhutbaResource;

class ListKhutbas extends ListRecords
{
    protected static string $resource = KhutbaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
