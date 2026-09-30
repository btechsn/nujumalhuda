<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Définit le scope d'organisation pour les requêtes.
 *
 * Permet de filtrer automatiquement les données selon l'organisation
 * de l'utilisateur authentifié (centre, dahira, etc.).
 *
 * Usage : Route::middleware(['auth:sanctum', 'organization.scope'])
 */
class SetOrganizationScope
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, ?string $organizationType = null): Response
    {
        if ($user = $request->user()) {
            // L'organisation courante peut être déterminée par :
            // - Un header X-Organization-Id
            // - Le dernier membership actif de l'utilisateur
            // - L'organisation par défaut de son profil

            $organizationId = $request->header('X-Organization-Id');

            if ($organizationId) {
                // Vérifier que l'utilisateur est membre de cette organisation
                $membership = $user->memberships()
                    ->where('organization_id', $organizationId)
                    ->where('status', 'active')
                    ->first();

                if ($membership) {
                    // Stocker l'organisation courante dans la requête
                    $request->merge(['current_organization_id' => $organizationId]);
                    $request->merge(['current_membership' => $membership]);

                    // Optionnel : vérifier le type d'organisation si spécifié
                    if ($organizationType && $membership->organization->type !== $organizationType) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Invalid organization type',
                        ], 403);
                    }
                }
            }
        }

        return $next($request);
    }
}
