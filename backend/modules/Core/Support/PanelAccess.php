<?php

declare(strict_types=1);

namespace Modules\Core\Support;

use Modules\Core\Models\User;

/**
 * Visibilité du panneau d'administration selon le rôle.
 * L'administrateur général voit tout. Les autres ne voient que leur périmètre.
 */
final class PanelAccess
{
    public static function allows(string $section): bool
    {
        $user = auth()->user();
        if (!$user instanceof User) {
            return false;
        }

        $roles = $user->activeRoleNames();
        if (in_array('admin', $roles, true)) {
            return true;
        }

        $allowed = match ($section) {
            'education' => ['director', 'secretary', 'academic_admin'],
            'academics' => ['director', 'teacher', 'academic_admin'],
            'zawiya' => ['director', 'secretary'],
            'live' => ['director', 'teacher'],
            'live_setup' => ['director'],
            'social' => ['director', 'secretary'],
            'news' => ['director', 'secretary'],
            'community' => ['director', 'secretary'],
            'dahira_office' => ['director', 'secretary', 'dahira_muqaddam', 'dahira_secretary'],
            'dahira_money' => ['director', 'dahira_muqaddam', 'dahira_treasurer'],
            'resources' => ['director', 'teacher', 'academic_admin'],
            'compliance' => ['director'],
            'pages' => ['director', 'secretary'],
            'settings_public' => ['director', 'secretary'],
            'settings_editorial' => ['director'],
            'newsletter' => ['director', 'secretary'],
            'dashboard_money' => ['director'],
            'dashboard_activity' => ['director', 'secretary', 'teacher', 'academic_admin'],
            default => [],
        };

        return array_intersect($roles, $allowed) !== [];
    }

    /**
     * Organisations dahira visibles.
     * null : l'administration du centre voit toutes les demandes.
     *
     * @param  list<string>  $roles
     * @return list<string>|null
     */
    public static function dahiraOrganizationIds(array $roles): ?array
    {
        $user = auth()->user();
        if (!$user instanceof User) {
            return [];
        }

        if ($user->hasAnyRole(['admin', 'director', 'secretary'])) {
            return null;
        }

        return $user->memberships()
            ->where('status', 'active')
            ->whereHas('role', fn ($query) => $query->whereIn('name', $roles))
            ->pluck('organization_id')
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}
