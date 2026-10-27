<?php

declare(strict_types=1);

namespace Modules\Core\Filament\Resources\UserResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Modules\Core\Filament\Resources\UserResource;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    public function getTitle(): string
    {
        return 'Utilisateurs';
    }

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()->label('Créer un utilisateur')];
    }
}
