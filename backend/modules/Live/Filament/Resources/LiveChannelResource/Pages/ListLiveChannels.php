<?php

namespace Modules\Live\Filament\Resources\LiveChannelResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Modules\Live\Filament\Resources\LiveChannelResource;

class ListLiveChannels extends ListRecords
{
    protected static string $resource = LiveChannelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
