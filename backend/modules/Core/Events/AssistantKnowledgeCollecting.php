<?php

declare(strict_types=1);

namespace Modules\Core\Events;

use Illuminate\Foundation\Events\Dispatchable;

/**
 * Les modules ajoutent des fiches publiques. L'assistant ne répond qu'avec elles.
 */
class AssistantKnowledgeCollecting
{
    use Dispatchable;

    /**
     * @param  list<array{id: string, title: string, body: string, url: ?string}>  $chunks
     */
    public function __construct(
        public string $locale,
        public array $chunks = [],
    ) {
    }

    public function add(string $id, string $title, string $body, ?string $url = null): void
    {
        $title = trim($title);
        $body = trim($body);
        if ($title === '' && $body === '') {
            return;
        }

        $this->chunks[] = [
            'id' => $id,
            'title' => $title,
            'body' => $body,
            'url' => $url,
        ];
    }
}
