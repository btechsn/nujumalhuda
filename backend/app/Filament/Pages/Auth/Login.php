<?php

declare(strict_types=1);

namespace App\Filament\Pages\Auth;

use Filament\Pages\Auth\Login as BaseLogin;

class Login extends BaseLogin
{
    protected static string $layout = 'filament.auth.layout';

    protected static string $view = 'filament.auth.login';
}
