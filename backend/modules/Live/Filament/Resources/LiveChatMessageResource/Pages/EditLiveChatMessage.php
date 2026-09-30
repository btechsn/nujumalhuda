<?php

namespace Modules\Live\Filament\Resources\LiveChatMessageResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Modules\Live\Filament\Resources\LiveChatMessageResource;

class EditLiveChatMessage extends EditRecord
{
    protected static string $resource = LiveChatMessageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
