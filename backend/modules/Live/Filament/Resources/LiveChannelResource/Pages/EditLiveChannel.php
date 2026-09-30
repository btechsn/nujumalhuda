<?php

namespace Modules\Live\Filament\Resources\LiveChannelResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Modules\Live\Filament\Resources\LiveChannelResource;

class EditLiveChannel extends EditRecord
{
    protected static string $resource = LiveChannelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
