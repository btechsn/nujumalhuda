<?php

namespace Modules\Mosque\Filament\Resources\PrayerTimeResource\Pages;

use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Modules\Mosque\Filament\Resources\PrayerTimeResource;
use Modules\Mosque\Services\PrayerTimeService;

class EditPrayerTime extends EditRecord
{
    protected static string $resource = PrayerTimeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('enregistrer')
                ->label('Enregistrer')
                ->color('primary')
                ->action('save'),
        ];
    }

    protected function getSaveFormAction(): Action
    {
        return parent::getSaveFormAction()->label('Enregistrer');
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $record = $this->getRecord();
        $manual = $data['manual_time'] ?? null;

        if (blank($manual)) {
            $data['manual_time'] = null;
            $data['is_overridden'] = false;
            $data['overridden_by'] = null;
            $data['overridden_at'] = null;

            return $data;
        }

        $previous = $record->manual_time;
        $changed = blank($previous)
            || Carbon::parse($previous)->format('H:i') !== Carbon::parse($manual)->format('H:i');

        $data['is_overridden'] = true;
        $data['overridden_by'] = auth()->id();

        if ($changed || ! $record->is_overridden) {
            $data['overridden_at'] = now();
        }

        return $data;
    }

    protected function afterSave(): void
    {
        $record = $this->getRecord()->refresh();
        $service = app(PrayerTimeService::class);
        $service->refreshIqama($record);
        $service->clearCacheForDate(
            $record->date->format('Y-m-d'),
            $record->organization_id,
        );
    }
}
