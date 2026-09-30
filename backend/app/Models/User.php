<?php

declare(strict_types=1);

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;

class User extends \Modules\Core\Models\User implements FilamentUser
{
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->isStaff();
    }
}
