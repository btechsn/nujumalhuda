<?php

namespace Modules\Community\Filament\Resources\SmsDigestSubscriberResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Modules\Community\Filament\Resources\SmsDigestSubscriberResource;

class ListSmsDigestSubscribers extends ListRecords
{
    protected static string $resource = SmsDigestSubscriberResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
