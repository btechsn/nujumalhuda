<?php

namespace Modules\Community\Filament\Resources\CommunityEventResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Modules\Community\Filament\Resources\CommunityEventResource;

class EditCommunityEvent extends EditRecord
{
    protected static string $resource = CommunityEventResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
