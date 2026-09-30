<?php

namespace Modules\Community\Filament\Resources\SmsDigestSubscriberResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Modules\Community\Filament\Resources\SmsDigestSubscriberResource;

class EditSmsDigestSubscriber extends EditRecord
{
    protected static string $resource = SmsDigestSubscriberResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
