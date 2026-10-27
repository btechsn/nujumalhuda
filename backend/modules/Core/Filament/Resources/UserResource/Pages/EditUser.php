<?php

declare(strict_types=1);

namespace Modules\Core\Filament\Resources\UserResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Modules\Core\Filament\Resources\UserResource;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    public function getTitle(): string
    {
        return 'Gérer l’utilisateur';
    }

    protected function getHeaderActions(): array
    {
        return [Actions\Action::make('back')
            ->label('Retour à la liste')
            ->icon('heroicon-o-arrow-left')
            ->url(UserResource::getUrl('index'))];
    }

    protected function getRedirectUrl(): string
    {
        return UserResource::getUrl('index');
    }
}
