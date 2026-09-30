<?php

namespace Modules\Live\Filament\Resources\LiveStreamResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Modules\Live\Filament\Resources\LiveStreamResource;

class ListLiveStreams extends ListRecords
{
    protected static string $resource = LiveStreamResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
