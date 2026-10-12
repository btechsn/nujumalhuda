<?php

declare(strict_types=1);

namespace Modules\Core\Events;

use Illuminate\Foundation\Events\Dispatchable;

/**
 * La recherche publique interroge les modules sans que Core importe leurs modèles.
 */
class PublicSearchRunning
{
    use Dispatchable;

    /**
     * @param  list<array{kind: string, title: string, url: string, fell_back: bool}>  $results
     */
    public function __construct(
        public string $query,
        public string $locale,
        public array $results = [],
    ) {
    }
}
