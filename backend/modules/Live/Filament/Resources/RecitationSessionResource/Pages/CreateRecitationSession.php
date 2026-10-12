<?php

declare(strict_types=1);

namespace Modules\Live\Filament\Resources\RecitationSessionResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\Live\Filament\Resources\RecitationSessionResource;

class CreateRecitationSession extends CreateRecord
{
    protected static string $resource = RecitationSessionResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return RecitationSessionResource::enforceConsent($data);
    }
}
