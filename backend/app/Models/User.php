<?php

declare(strict_types=1);

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;

class User extends \Modules\Core\Models\User implements FilamentUser
{
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->disabled_at === null && $this->hasAnyRole([
            'admin',
            'director',
            'secretary',
            'teacher',
            'academic_admin',
            'dahira_muqaddam',
            'dahira_secretary',
            'dahira_treasurer',
        ]);
    }
}
