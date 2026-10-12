<?php

declare(strict_types=1);

namespace Modules\Core\Events;

use Illuminate\Foundation\Events\Dispatchable;

/**
 * La supervision interroge les modules sans que Core importe leurs modèles.
 */
class OperationsStatusCollecting
{
    use Dispatchable;

    /**
     * @param  array<string, mixed>  $flags
     */
    public function __construct(public array $flags = [])
    {
    }
}
