<?php

declare(strict_types=1);

namespace Modules\Live\Filament\Resources\RecitationSessionResource\Pages;

use Filament\Resources\Pages\EditRecord;
use Modules\Live\Filament\Resources\RecitationSessionResource;

class EditRecitationSession extends EditRecord
{
    protected static string $resource = RecitationSessionResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return RecitationSessionResource::enforceConsent($data);
    }

    protected function getHeaderActions(): array
    {
        return [\Filament\Actions\DeleteAction::make()];
    }
}
