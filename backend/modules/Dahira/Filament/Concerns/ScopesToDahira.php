<?php

declare(strict_types=1);

namespace Modules\Dahira\Filament\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Modules\Core\Support\PanelAccess;

trait ScopesToDahira
{
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $organizations = PanelAccess::dahiraOrganizationIds(static::officerRoles());

        if ($organizations === null) {
            return $query;
        }

        return match (static::dahiraScope()) {
            'organization' => $query->whereIn('organization_id', $organizations),
            'plan' => $query->whereHas(
                'plan.group',
                fn (Builder $group) => $group->whereIn('organization_id', $organizations),
            ),
            default => $query->whereHas(
                'group',
                fn (Builder $group) => $group->whereIn('organization_id', $organizations),
            ),
        };
    }

    /**
     * @return list<string>
     */
    abstract protected static function officerRoles(): array;

    protected static function dahiraScope(): string
    {
        return 'group';
    }
}
