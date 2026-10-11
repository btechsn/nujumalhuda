<?php

namespace Modules\Live\Filament\Resources\LiveChannelResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Modules\Live\Filament\Resources\LiveChannelResource;

class ListLiveChannels extends ListRecords
{
    protected static string $resource = LiveChannelResource::class;

    public function getSubheading(): ?string
    {
        return 'Les canaux servent à classer les séances. Le direct affiché sur le site vient de YouTube.';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
