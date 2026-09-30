<?php

declare(strict_types=1);

namespace Modules\Announcements\Data;

use Modules\Announcements\Enums\AnnouncementCategory;
use Modules\Announcements\Enums\AnnouncementPriority;

class AnnouncementData
{
    public function __construct(
        public ?string $id,
        public array $title,
        public array $message,
        public AnnouncementCategory $category,
        public AnnouncementPriority $priority,
        public ?\DateTimeInterface $starts_at,
        public ?\DateTimeInterface $ends_at,
        public bool $is_active,
        public ?string $action_url,
        public ?array $audiences,
    ) {
    }

    public static function from(array $payload): self
    {
        $category = $payload['category'];
        $priority = $payload['priority'];

        return new self(
            id: $payload['id'] ?? null,
            title: $payload['title'],
            message: $payload['message'],
            category: is_string($category) ? AnnouncementCategory::from($category) : $category,
            priority: is_string($priority) ? AnnouncementPriority::from($priority) : $priority,
            starts_at: $payload['starts_at'] ?? null,
            ends_at: $payload['ends_at'] ?? null,
            is_active: (bool) ($payload['is_active'] ?? true),
            action_url: $payload['action_url'] ?? null,
            audiences: $payload['audiences'] ?? null,
        );
    }
}
