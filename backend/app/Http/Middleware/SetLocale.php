<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

/**
 * Définit la locale de l'application.
 *
 * Ordre de priorité :
 * 1. Entête Accept-Language de la requête
 * 2. Profil utilisateur authentifié
 * 3. Locale par défaut (fr)
 */
class SetLocale
{
    /**
     * Locales supportées.
     */
    protected array $supportedLocales = ['fr', 'en', 'ar'];

    /**
     * Locale par défaut.
     */
    protected string $defaultLocale = 'fr';

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $this->determineLocale($request);

        App::setLocale($locale);

        return $next($request);
    }

    /**
     * Détermine la locale à utiliser.
     */
    protected function determineLocale(Request $request): string
    {
        // 1. Utilisateur authentifié avec préférence de langue
        if ($user = $request->user()) {
            $userLocale = $user->locale ?? null;
            if ($userLocale && in_array($userLocale, $this->supportedLocales)) {
                return $userLocale;
            }
        }

        // 2. Entête Accept-Language
        $acceptLanguage = $request->header('Accept-Language');
        if ($acceptLanguage) {
            $locale = $this->parseAcceptLanguage($acceptLanguage);
            if ($locale) {
                return $locale;
            }
        }

        // 3. Locale par défaut
        return $this->defaultLocale;
    }

    /**
     * Parse l'entête Accept-Language et retourne la première locale supportée.
     */
    protected function parseAcceptLanguage(string $header): ?string
    {
        // Format: "fr-FR,fr;q=0.9,en-US;q=0.8,en;q=0.7,ar;q=0.6"
        $languages = explode(',', $header);

        foreach ($languages as $language) {
            $parts = explode(';', $language);
            $code = strtolower(trim($parts[0]));

            // Extraire le code de langue principal (fr-FR -> fr)
            $primaryCode = explode('-', $code)[0];

            if (in_array($primaryCode, $this->supportedLocales)) {
                return $primaryCode;
            }
        }

        return null;
    }
}
