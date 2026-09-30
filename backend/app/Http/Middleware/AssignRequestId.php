<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * Assigne un ID unique à chaque requête pour la corrélation des logs.
 *
 * L'ID est ajouté aux headers de réponse et peut être utilisé dans les logs.
 */
class AssignRequestId
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $requestId = $request->header('X-Request-ID') ?? (string) Str::ulid();

        // Stocker dans la requête pour utilisation dans les logs
        $request->attributes->set('request_id', $requestId);

        // Définir dans le contexte de log global
        if (function_exists('logger')) {
            logger()->withContext(['request_id' => $requestId]);
        }

        $response = $next($request);

        // Ajouter à la réponse
        if ($response instanceof Response) {
            $response->headers->set('X-Request-ID', $requestId);
        }

        return $response;
    }
}
