<?php

namespace Modules\Community\Filament\Resources\CommunityEventResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Modules\Community\Filament\Resources\CommunityEventResource;

class ListCommunityEvents extends ListRecords
{
    protected static string $resource = CommunityEventResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
