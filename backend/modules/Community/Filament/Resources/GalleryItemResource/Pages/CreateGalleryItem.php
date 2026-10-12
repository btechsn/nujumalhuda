<?php

namespace Modules\Community\Filament\Resources\GalleryItemResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\Community\Filament\Resources\GalleryItemResource;

class CreateGalleryItem extends CreateRecord
{
    protected static string $resource = GalleryItemResource::class;

    public function getTitle(): string
    {
        return 'Nouveau média';
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return GalleryItemResource::enforceConsent($data);
    }
}
