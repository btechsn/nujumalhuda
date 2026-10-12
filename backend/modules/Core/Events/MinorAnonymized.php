<?php

declare(strict_types=1);

namespace Modules\Core\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Modules\Core\Models\User;

/**
 * L'élève est déjà anonymisé. Les autres modules retirent les copies
 * de son identité (PDF, galerie, récitation, archive vidéo) sans que
 * Core ait à connaître leurs modèles.
 */
class MinorAnonymized
{
    use Dispatchable;

    public function __construct(public User $user)
    {
    }
}
