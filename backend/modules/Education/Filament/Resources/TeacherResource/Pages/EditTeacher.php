<?php

namespace Modules\Education\Filament\Resources\TeacherResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Modules\Education\Filament\Resources\TeacherResource;

class EditTeacher extends EditRecord
{
    protected static string $resource = TeacherResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $user = $this->getRecord()->user;

        $data['first_name'] = $user?->first_name;
        $data['last_name'] = $user?->last_name;
        $data['email'] = $user?->email;
        $data['phone'] = $user?->phone;
        $photoPath = $this->getRecord()->photo?->path;
        $data['photo'] = filled($photoPath) ? [$photoPath] : [];

        $details = $this->getRecord()->ijaza_details;
        $french = is_array($details) && is_array($details['fr'] ?? null) ? $details['fr'] : [];
        $data['ijaza_title'] = $french['title'] ?? '';
        $data['ijaza_issuer'] = $french['issuer'] ?? '';
        $data['ijaza_year'] = $french['year'] ?? '';
        $data['ijaza_domain'] = $french['domain'] ?? '';
        $document = is_array($details) ? ($details['document'] ?? null) : null;
        $data['ijaza_document'] = is_string($document) && $document !== '' ? [$document] : [];
        $data['sanad'] = TeacherResource::sanadText($data['sanad'] ?? null);

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->getRecord()->user?->update([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'phone' => filled($data['phone'] ?? null) ? $data['phone'] : null,
        ]);

        $data = TeacherResource::applyPhoto(
            $data,
            $this->getRecord()->getKey(),
            $this->getRecord()->photo_media_id,
        );
        $details = $this->getRecord()->ijaza_details;
        $data = TeacherResource::applyIjaza($data, is_array($details) ? $details : null);
        $data = TeacherResource::applySanad($data, $this->getRecord()->sanad);

        unset($data['first_name'], $data['last_name'], $data['email'], $data['phone']);

        return $data;
    }
}
