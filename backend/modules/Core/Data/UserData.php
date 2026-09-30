<?php

declare(strict_types=1);

namespace Modules\Core\Data;

class UserData
{
    public function __construct(
        public ?string $id,
        public string $first_name,
        public string $last_name,
        public ?string $email,
        public ?string $phone,
        public string $locale,
        public string $timezone,
        public ?string $avatar,
    ) {
    }

    public static function from(array $payload): self
    {
        return new self(
            id: $payload['id'] ?? null,
            first_name: (string) $payload['first_name'],
            last_name: (string) $payload['last_name'],
            email: $payload['email'] ?? null,
            phone: $payload['phone'] ?? null,
            locale: (string) ($payload['locale'] ?? 'fr'),
            timezone: (string) ($payload['timezone'] ?? 'Africa/Dakar'),
            avatar: $payload['avatar'] ?? null,
        );
    }
}
