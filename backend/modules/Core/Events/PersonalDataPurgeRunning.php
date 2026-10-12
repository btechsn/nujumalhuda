<?php

declare(strict_types=1);

namespace Modules\Core\Events;

use Illuminate\Foundation\Events\Dispatchable;

/**
 * Déclenchée par `privacy:purge` une fois les tables du module Core
 * nettoyées. Les autres modules y ajoutent leurs propres compteurs :
 * Core ne connaît pas leurs modèles.
 */
class PersonalDataPurgeRunning
{
    use Dispatchable;

    /**
     * @param  array<string, int>  $purged
     */
    public function __construct(public array $purged = [])
    {
    }
}
