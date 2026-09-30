<?php

namespace Modules\Community\Filament\Resources\DiscussionMessageResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Modules\Community\Filament\Resources\DiscussionMessageResource;

class EditDiscussionMessage extends EditRecord
{
    protected static string $resource = DiscussionMessageResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
