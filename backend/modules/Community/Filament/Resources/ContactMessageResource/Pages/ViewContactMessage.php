<?php

namespace Modules\Community\Filament\Resources\ContactMessageResource\Pages;

use Filament\Resources\Pages\ViewRecord;
use Modules\Community\Filament\Resources\ContactMessageResource;
use Modules\Community\Models\ContactMessage;

class ViewContactMessage extends ViewRecord
{
    protected static string $resource = ContactMessageResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        /** @var ContactMessage $record */
        $record = $this->getRecord();
        if ($record->status === 'new') {
            $record->update(['status' => 'read', 'read_at' => now()]);
        }

        return $data;
    }
}
