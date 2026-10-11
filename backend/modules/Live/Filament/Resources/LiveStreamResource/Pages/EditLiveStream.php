<?php

namespace Modules\Live\Filament\Resources\LiveStreamResource\Pages;

use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Modules\Live\Filament\Resources\LiveStreamResource;

class EditLiveStream extends EditRecord
{
    protected static string $resource = LiveStreamResource::class;

    public function getTitle(): string
    {
        return 'Modifier la séance';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('end_live')
                ->label('Terminer le direct')
                ->icon('heroicon-o-stop')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Terminer le direct')
                ->modalDescription('La séance ne s’affichera plus comme étant en direct sur le site.')
                ->visible(fn () => $this->getRecord()->isLive())
                ->action(function () {
                    $this->getRecord()->markEnded();
                    $this->refreshFormData(['status']);

                    Notification::make()
                        ->title('Direct terminé')
                        ->success()
                        ->send();
                }),

            Actions\DeleteAction::make()
                ->disabled(fn ($record) => $record->isLive()),
        ];
    }
}
