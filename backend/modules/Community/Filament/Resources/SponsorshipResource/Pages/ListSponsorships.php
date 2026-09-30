<?php

namespace Modules\Community\Filament\Resources\SponsorshipResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Modules\Community\Filament\Resources\SponsorshipResource;

class ListSponsorships extends ListRecords
{
    protected static string $resource = SponsorshipResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
