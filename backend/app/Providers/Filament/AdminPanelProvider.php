<?php

declare(strict_types=1);

namespace App\Providers\Filament;

use Filament\Enums\ThemeMode;
use Filament\Navigation\NavigationGroup;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\HtmlString;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

/**
 * Panneau d'administration.
 *
 * Les échelles de couleur sont déclarées en canaux RVB parce que Filament
 * les compose en `rgb(var(--primary-500) / <alpha>)` pour gérer la
 * transparence. Ce sont exactement les valeurs de `design/tokens.css`,
 * reportées dans le format attendu — le thème CSS, lui, importe le fichier
 * de jetons directement.
 *
 * Les ressources ne sont pas déclarées ici : chaque module expose les
 * siennes depuis son propre service provider, ce qui permet d'activer ou
 * de désactiver un module sans toucher à ce fichier.
 */
final class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('institut/administration')
            ->login(\App\Filament\Pages\Auth\Login::class)
            ->passwordReset()
            ->profile(\App\Filament\Pages\Auth\EditProfile::class, isSimple: false)
            ->pages([
                \App\Filament\Pages\Dashboard::class,
                \App\Filament\Pages\Settings\ContactSettingsPage::class,
                \App\Filament\Pages\Settings\FooterSettingsPage::class,
                \App\Filament\Pages\Settings\LinksSettingsPage::class,
                \App\Filament\Pages\SendNewsletter::class,
                \App\Filament\Pages\Settings\AnalyticsSettingsPage::class,
                \App\Filament\Pages\Settings\MailSettingsPage::class,
                \App\Filament\Pages\Settings\SmsSettingsPage::class,
                \App\Filament\Pages\Settings\WaveSettingsPage::class,
                \App\Filament\Pages\Settings\OrangeMoneySettingsPage::class,
                \App\Filament\Pages\ManageSettings::class,
                ...\Modules\Core\Support\PlatformPages::pageClasses(),
            ])
            ->discoverWidgets(
                in: app_path('Filament/Widgets'),
                for: 'App\\Filament\\Widgets',
            )

            /* ── Identité ────────────────────────────────────────────── */
            ->brandName('Nujum Al-Huda')
            ->brandLogo(fn (): HtmlString => new HtmlString(view('filament.brand')->render()))
            ->darkModeBrandLogo(fn (): HtmlString => new HtmlString(view('filament.brand')->render()))
            ->brandLogoHeight('auto')
            ->favicon(asset('brand/logo.jpeg'))
            ->sidebarCollapsibleOnDesktop()
            ->renderHook(
                PanelsRenderHook::STYLES_AFTER,
                fn (): HtmlString => new HtmlString(<<<'HTML'
                    <style>
                        .fi-logo { width: auto !important; height: auto !important; border-radius: 0; object-fit: unset; }
                        .nh-brand { display: flex; align-items: center; gap: .65rem; min-width: 0; }
                        .nh-brand-mark { width: 2.5rem; height: 2.5rem; flex: none; border-radius: 999px; object-fit: cover; object-position: center 28%; }
                        .nh-brand-copy { display: flex; flex-direction: column; line-height: 1.05; min-width: 0; }
                        .nh-brand-copy strong { font-size: .95rem; font-weight: 800; letter-spacing: -.01em; }
                        .nh-brand-copy span { font-size: .68rem; font-weight: 600; opacity: .72; }
                        .fi-user-avatar { width: 2.25rem; height: 2.25rem; object-fit: cover; }
                        .fi-sidebar-header .fi-icon-btn { display: none !important; }
                        .fi-sidebar-group-icon { color: rgb(var(--gray-600)); }
                        .fi-sidebar-nav-groups { flex: 1; }
                        .fi-sidebar-group[data-group-label="Pages"] { margin-top: auto; }
                        .fi-sidebar:not(.fi-sidebar-open) .fi-sidebar-header {
                            justify-content: center;
                            padding-inline: 0;
                        }
                        .fi-sidebar:not(.fi-sidebar-open) .fi-sidebar-header > div {
                            display: flex !important;
                            opacity: 1 !important;
                            width: 100%;
                            justify-content: center;
                        }
                        .fi-sidebar:not(.fi-sidebar-open) .nh-brand {
                            justify-content: center;
                            gap: 0;
                        }
                        .fi-sidebar:not(.fi-sidebar-open) .nh-brand-copy {
                            display: none !important;
                        }
                    </style>
                HTML),
            )

            /* ── Palette ─────────────────────────────────────────────────
             * `gray` est volontairement hybride : ses nuances claires sont
             * l'ivoire du site, ses nuances sombres l'encre teintée de vert.
             * Les deux modes restent ainsi dans la marque, au prix d'une
             * échelle qui n'est pas parfaitement uniforme en son milieu.
             */
            ->colors([
                'primary' => [
                    50 => '238, 247, 241',
                    100 => '212, 235, 221',
                    200 => '169, 215, 187',
                    300 => '118, 190, 149',
                    400 => '69, 161, 114',
                    500 => '31, 133, 83',
                    600 => '15, 109, 65',
                    700 => '11, 90, 49',
                    800 => '9, 70, 39',
                    900 => '7, 55, 31',
                    950 => '3, 28, 15',
                ],
                'gold' => [
                    50 => '251, 248, 235',
                    100 => '246, 239, 208',
                    200 => '238, 222, 161',
                    300 => '227, 200, 109',
                    400 => '214, 177, 67',
                    500 => '200, 151, 31',
                    600 => '166, 124, 25',
                    700 => '130, 97, 28',
                    800 => '103, 78, 28',
                    900 => '85, 64, 27',
                    950 => '46, 34, 12',
                ],
                'gray' => [
                    50 => '253, 252, 247',
                    100 => '250, 246, 236',
                    200 => '242, 236, 221',
                    300 => '229, 220, 199',
                    400 => '203, 192, 166',
                    500 => '169, 157, 128',
                    600 => '134, 123, 97',
                    700 => '33, 54, 43',
                    800 => '16, 28, 23',
                    900 => '10, 20, 16',
                    950 => '6, 13, 10',
                ],
                'success' => Color::Emerald,
                'warning' => Color::Amber,
                'danger' => Color::Red,
                'info' => Color::Blue,
            ])

            /* ── Thème ───────────────────────────────────────────────────
             * Les couleurs de marque sont déjà déclarées ci-dessus.
             * Le fichier resources/css/filament/admin/theme.css sera repris
             * quand le build Vite du panneau sera en place.
             */
            ->font('IBM Plex Sans')
            ->defaultThemeMode(ThemeMode::System)

            /* ── Navigation ──────────────────────────────────────────────
             * Les groupes reprennent le découpage en modules : un
             * administrateur et un développeur parlent ainsi de la même
             * structure.
             */
            ->navigationGroups([
                NavigationGroup::make('Éducation')->icon('heroicon-o-academic-cap'),
                NavigationGroup::make('Suivi pédagogique')->icon('heroicon-o-bookmark'),
                NavigationGroup::make('Zawiya')->icon('heroicon-o-building-library'),
                NavigationGroup::make('Live Streaming')->icon('heroicon-o-video-camera'),
                NavigationGroup::make('Live réseaux sociaux')->icon('heroicon-o-share'),
                NavigationGroup::make('Actualités')->icon('heroicon-o-newspaper'),
                NavigationGroup::make('Communauté')->icon('heroicon-o-chat-bubble-left-right'),
                NavigationGroup::make('Dahira')->icon('heroicon-o-user-group'),
                NavigationGroup::make('Ressources')->icon('heroicon-o-book-open'),
                NavigationGroup::make('Pages')->icon('heroicon-o-document-text')->collapsible(),
                NavigationGroup::make('Paramètres')->icon('heroicon-o-cog-6-tooth')->collapsible(),
            ])

            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
                \App\Http\Middleware\SetLocale::class,
                \App\Http\Middleware\SetOrganizationScope::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
