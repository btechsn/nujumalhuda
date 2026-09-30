<?php

declare(strict_types=1);

namespace Modules\Core\Data;

use Modules\Core\Enums\MembershipStatus;

class MembershipData
{
    public function __construct(
        public ?string $id,
        public string $user_id,
        public string $organization_id,
        public string $role_id,
        public MembershipStatus $status,
        public ?\DateTimeInterface $joined_at,
        public ?\DateTimeInterface $expires_at,
        public ?string $notes,
    ) {
    }

    public static function from(array $payload): self
    {
        $status = $payload['status'] ?? MembershipStatus::PENDING;
        if (is_string($status)) {
            $status = MembershipStatus::from($status);
        }

        return new self(
            id: $payload['id'] ?? null,
            user_id: (string) $payload['user_id'],
            organization_id: (string) $payload['organization_id'],
            role_id: (string) $payload['role_id'],
            status: $status,
            joined_at: $payload['joined_at'] ?? null,
            expires_at: $payload['expires_at'] ?? null,
            notes: $payload['notes'] ?? null,
        );
    }
}
