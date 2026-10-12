<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->trustProxies(at: '*');
        $middleware->api(prepend: [
            \App\Http\Middleware\AssignRequestId::class,
            \App\Http\Middleware\SetLocale::class,
        ]);

        // Cette application n'a pas de route web nommée "login" : l'API est
        // consommée par le frontend Next.js, et l'admin passe par Filament
        // (qui gère son propre guard). Sans cette ligne, Laravel tente par
        // défaut `route('login')` pour toute requête non authentifiée qui
        // n'envoie pas `Accept: application/json`, ce qui provoque une 500
        // ("Route [login] not defined.") au lieu d'un 401 propre.
        $middleware->redirectGuestsTo(fn () => null);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->shouldRenderJsonWhen(function ($request) {
            return $request->is('api/*') || $request->expectsJson();
        });
    })->create();
