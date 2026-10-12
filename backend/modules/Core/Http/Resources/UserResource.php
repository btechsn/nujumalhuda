<?php

declare(strict_types=1);

namespace Modules\Core\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;
use Modules\Core\Enums\OrganizationType;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $activeDahiraMemberships = $this->memberships()
            ->active()
            ->whereHas('organization', fn ($query) => $query->where('type', OrganizationType::DAHIRA))
            ->with('organization')
            ->get();

        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'full_name' => $this->fullName(),
            'email' => $this->email,
            'phone' => $this->phone,
            'locale' => $this->locale,
            'timezone' => $this->timezone,
            'avatar' => $this->avatar,
            'email_verified_at' => $this->email_verified_at?->toISOString(),
            'phone_verified_at' => $this->phone_verified_at?->toISOString(),
            'last_login_at' => $this->last_login_at?->toISOString(),
            'created_at' => $this->created_at->toISOString(),

            // Rôles, utilisés côté portail pour filtrer le menu et le tableau de bord.
            'is_staff' => $this->isStaff(),
            'is_teacher' => DB::table('teachers')->where('id', $this->id)->whereNull('deleted_at')->exists()
                || $this->memberships()->where('status', 'active')->whereHas('role', fn ($query) => $query->where('name', 'teacher'))->exists(),
            'is_guardian' => $this->wards()->exists(),
            'is_minor' => $this->isMinorCandidate(),
            'is_dahira_member' => $activeDahiraMemberships->isNotEmpty(),
            'roles' => $this->memberships()
                ->active()
                ->with('role')
                ->get()
                ->pluck('role.name')
                ->filter()
                ->unique()
                ->values(),
            'wards' => $this->wards()
                ->with('ward')
                ->get()
                ->map(fn ($guardianship) => [
                    'id' => $guardianship->ward_id,
                    'full_name' => $guardianship->ward?->fullName(),
                ])
                ->values(),
            'dahira_groups' => $activeDahiraMemberships
                ->map(fn ($membership) => [
                    'id' => $membership->organization_id,
                    'name' => $membership->organization?->name,
                ])
                ->values(),
        ];
    }
}
