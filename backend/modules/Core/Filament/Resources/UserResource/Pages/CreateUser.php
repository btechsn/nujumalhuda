<?php

declare(strict_types=1);

namespace Modules\Core\Filament\Resources\UserResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\Core\Filament\Resources\UserResource;
use Modules\Core\Models\Membership;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    /** @var array{organization_id: string, role_id: string}|null */
    private ?array $initialMembership = null;

    public function getTitle(): string
    {
        return 'Créer un utilisateur';
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->initialMembership = [
            'organization_id' => (string) $data['initial_organization_id'],
            'role_id' => (string) $data['initial_role_id'],
        ];

        unset($data['initial_organization_id'], $data['initial_role_id']);

        return $data;
    }

    protected function afterCreate(): void
    {
        Membership::create([
            'user_id' => $this->record->getKey(),
            'organization_id' => $this->initialMembership['organization_id'],
            'role_id' => $this->initialMembership['role_id'],
            'status' => 'active',
            'joined_at' => now(),
        ]);
    }
}
