<?php

namespace Modules\News\Filament\Resources\ArticleResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\Core\Models\Media;
use Modules\News\Filament\Resources\ArticleResource;

class CreateArticle extends CreateRecord
{
    protected static string $resource = ArticleResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (blank($data['organization_id'] ?? null)) {
            $data['organization_id'] = ArticleResource::currentOrganizationId();
        }

        return ArticleResource::applyCover($data);
    }

    protected function afterCreate(): void
    {
        if ($this->record->cover_image_id) {
            Media::query()->whereKey($this->record->cover_image_id)->update([
                'mediable_id' => $this->record->getKey(),
            ]);
        }
    }
}
