<?php

namespace Modules\Community\Filament\Resources\DiscussionMessageResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Modules\Community\Filament\Resources\DiscussionMessageResource;

class ListDiscussionMessages extends ListRecords
{
    protected static string $resource = DiscussionMessageResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
