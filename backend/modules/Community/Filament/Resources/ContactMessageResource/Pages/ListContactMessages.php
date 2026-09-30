<?php

namespace Modules\Community\Filament\Resources\ContactMessageResource\Pages;

use Filament\Resources\Pages\ListRecords;
use Modules\Community\Filament\Resources\ContactMessageResource;

class ListContactMessages extends ListRecords
{
    protected static string $resource = ContactMessageResource::class;
}
