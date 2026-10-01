<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Filament\Pages\Settings\ContactSettingsPage;
use Filament\Pages\Page;

class ManageSettings extends Page
{
    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $slug = 'parametres';

    protected static string $view = 'filament.pages.redirect-settings';

    public function mount(): void
    {
        $this->redirect(ContactSettingsPage::getUrl());
    }
}
