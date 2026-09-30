<?php

namespace Modules\Community\Filament\Resources\ContactMessageResource\Pages;

use Filament\Resources\Pages\EditRecord;
use Modules\Community\Filament\Resources\ContactMessageResource;

class EditContactMessage extends EditRecord
{
    protected static string $resource = ContactMessageResource::class;
}
