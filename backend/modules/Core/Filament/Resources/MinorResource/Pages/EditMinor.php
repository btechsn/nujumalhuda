<?php

declare(strict_types=1);

namespace Modules\Core\Filament\Resources\MinorResource\Pages;

use Filament\Resources\Pages\EditRecord;
use Modules\Core\Filament\Resources\MinorResource;
use Modules\Core\Models\AuditLog;
use Modules\Core\Models\MinorConsent;

class EditMinor extends EditRecord
{
    protected static string $resource = MinorResource::class;

    /** @var array<string, mixed> */
    protected array $pendingConsent = [];

    public function getTitle(): string
    {
        return 'Consentement parental';
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    /**
     * Les trois cases et la note vivent dans `minor_consents`, pas sur
     * `users` : on les ajoute manuellement au tableau rempli par Filament.
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        AuditLog::log('minor.viewed', $this->record, context: ['user_id' => $this->record->id]);

        $consent = $this->record->minorConsent;

        $data['gallery_image_consent'] = (bool) $consent?->gallery_image_consent;
        $data['recitation_audio_consent'] = (bool) $consent?->recitation_audio_consent;
        $data['public_name_consent'] = (bool) $consent?->public_name_consent;
        $data['consent_notes'] = $consent?->notes;

        return $data;
    }

    /**
     * On retire ces clés avant que Filament ne les écrive sur `users`
     * (elles n'y existent pas), et on les garde de côté pour `afterSave`.
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->pendingConsent = [
            'gallery_image_consent' => (bool) ($data['gallery_image_consent'] ?? false),
            'recitation_audio_consent' => (bool) ($data['recitation_audio_consent'] ?? false),
            'public_name_consent' => (bool) ($data['public_name_consent'] ?? false),
            'notes' => $data['consent_notes'] ?? null,
        ];

        unset(
            $data['gallery_image_consent'],
            $data['recitation_audio_consent'],
            $data['public_name_consent'],
            $data['consent_notes'],
        );

        return $data;
    }

    protected function afterSave(): void
    {
        $existing = $this->record->minorConsent;
        $changed = !$existing
            || $existing->gallery_image_consent !== $this->pendingConsent['gallery_image_consent']
            || $existing->recitation_audio_consent !== $this->pendingConsent['recitation_audio_consent']
            || $existing->public_name_consent !== $this->pendingConsent['public_name_consent'];

        MinorConsent::updateOrCreate(
            ['user_id' => $this->record->id],
            [
                ...$this->pendingConsent,
                'consented_by' => $changed ? auth()->id() : ($existing?->consented_by),
                'consented_at' => $changed ? now() : ($existing?->consented_at),
            ],
        );

        AuditLog::log('minor.consent_updated', $this->record, newValues: [
            'gallery_image_consent' => $this->pendingConsent['gallery_image_consent'],
            'recitation_audio_consent' => $this->pendingConsent['recitation_audio_consent'],
            'public_name_consent' => $this->pendingConsent['public_name_consent'],
        ]);
    }
}
