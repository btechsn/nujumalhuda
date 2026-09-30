<?php

namespace Modules\Live\Filament\Resources\SocialAccountResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Modules\Live\Filament\Resources\SocialAccountResource;

class ListSocialAccounts extends ListRecords
{
    protected static string $resource = SocialAccountResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
