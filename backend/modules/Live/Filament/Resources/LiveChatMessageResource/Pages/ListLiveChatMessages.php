<?php

namespace Modules\Live\Filament\Resources\LiveChatMessageResource\Pages;

use Filament\Resources\Pages\ListRecords;
use Modules\Live\Filament\Resources\LiveChatMessageResource;

class ListLiveChatMessages extends ListRecords
{
    protected static string $resource = LiveChatMessageResource::class;
}
