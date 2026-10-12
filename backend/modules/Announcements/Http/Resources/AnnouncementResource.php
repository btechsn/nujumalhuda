<?php

declare(strict_types=1);

namespace Modules\Announcements\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Core\Support\EditorialText;

class AnnouncementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $locale = app()->getLocale();
        $title = EditorialText::present($this->resource->getTranslations('title'), $locale);
        $message = EditorialText::present($this->resource->getTranslations('message'), $locale);

        return [
            'id' => $this->id,
            'title' => $title['text'],
            'title_fell_back' => $title['fell_back'],
            'message' => $message['text'],
            'message_fell_back' => $message['fell_back'],
            'category' => [
                'value' => $this->category->value,
                'label' => $this->category->label(),
                'color' => $this->category->color(),
            ],
            'priority' => [
                'value' => $this->priority->value,
                'label' => $this->priority->label(),
            ],
            'action_url' => $this->action_url,
            'starts_at' => $this->starts_at?->toISOString(),
            'ends_at' => $this->ends_at?->toISOString(),
            'created_at' => $this->created_at->toISOString(),
        ];
    }
}
