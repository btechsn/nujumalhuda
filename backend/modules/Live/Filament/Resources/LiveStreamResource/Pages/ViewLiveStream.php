<?php

namespace Modules\Live\Filament\Resources\LiveStreamResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Modules\Live\Filament\Resources\LiveStreamResource;

class ViewLiveStream extends ViewRecord
{
    protected static string $resource = LiveStreamResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
