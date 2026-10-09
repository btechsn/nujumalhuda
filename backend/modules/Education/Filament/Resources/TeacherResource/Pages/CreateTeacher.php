<?php

namespace Modules\Education\Filament\Resources\TeacherResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;
use Modules\Core\Models\User;
use Modules\Education\Filament\Resources\TeacherResource;

class CreateTeacher extends CreateRecord
{
    protected static string $resource = TeacherResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = User::create([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'phone' => filled($data['phone'] ?? null) ? $data['phone'] : null,
            'password' => Str::password(32),
            'locale' => 'fr',
            'timezone' => 'Africa/Dakar',
        ]);

        $data['id'] = $user->id;
        $data = TeacherResource::applyPhoto($data, $user->id);
        $data = TeacherResource::applyIjaza($data);
        $data = TeacherResource::applySanad($data);

        unset($data['first_name'], $data['last_name'], $data['email'], $data['phone']);

        return $data;
    }
}
