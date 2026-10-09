<?php

namespace Modules\Education\Filament\Resources\ProgramTypeResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;
use Modules\Education\Filament\Resources\ProgramTypeResource;
use Modules\Education\Models\ProgramType;

class CreateProgramType extends CreateRecord
{
    protected static string $resource = ProgramTypeResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $name = is_array($data['name_i18n'] ?? null) ? ($data['name_i18n']['fr'] ?? '') : '';
        $base = Str::slug((string) ($data['slug'] ?? $name)) ?: 'parcours';
        $slug = $base;
        $suffix = 2;

        while (ProgramType::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        $data['slug'] = $slug;

        return $data;
    }
}
