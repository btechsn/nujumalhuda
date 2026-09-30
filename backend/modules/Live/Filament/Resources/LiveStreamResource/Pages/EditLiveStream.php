<?php

namespace Modules\Live\Filament\Resources\LiveStreamResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Modules\Live\Filament\Resources\LiveStreamResource;

class EditLiveStream extends EditRecord
{
    protected static string $resource = LiveStreamResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->disabled(fn ($record) => $record->isLive()),
        ];
    }
}
