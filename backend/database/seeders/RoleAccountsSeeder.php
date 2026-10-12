<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Core\Enums\MembershipStatus;
use Modules\Core\Enums\OrganizationType;
use Modules\Core\Models\Membership;
use Modules\Core\Models\Organization;
use Modules\Core\Models\Role;
use Modules\Core\Models\User;
use Modules\Education\Models\Teacher;

/**
 * Un compte de démonstration par rôle, mot de passe « password ».
 */
class RoleAccountsSeeder extends Seeder
{
    public function run(): void
    {
        $center = $this->center();
        $dahira = $this->dahira();

        $accounts = [
            ['email' => 'admin@nujumalhuda.com', 'first_name' => 'Admin', 'last_name' => 'Nujum', 'phone' => '+221770000001', 'role' => 'admin', 'label' => 'Administrateur', 'org' => $center],
            ['email' => 'directeur@nujumalhuda.com', 'first_name' => 'Directeur', 'last_name' => 'Nujum', 'phone' => '+221770000002', 'role' => 'director', 'label' => 'Directeur', 'org' => $center],
            ['email' => 'secretaire@nujumalhuda.com', 'first_name' => 'Secrétaire', 'last_name' => 'Nujum', 'phone' => '+221770000003', 'role' => 'secretary', 'label' => 'Secrétaire', 'org' => $center],
            ['email' => 'enseignant@nujumalhuda.com', 'first_name' => 'Enseignant', 'last_name' => 'Nujum', 'phone' => '+221770000004', 'role' => 'teacher', 'label' => 'Enseignant', 'org' => $center],
            ['email' => 'pedagogie@nujumalhuda.com', 'first_name' => 'Pédagogie', 'last_name' => 'Nujum', 'phone' => '+221770000005', 'role' => 'academic_admin', 'label' => 'Responsable pédagogique', 'org' => $center],
            ['email' => 'muqaddam@nujumalhuda.com', 'first_name' => 'Muqaddam', 'last_name' => 'Nujum', 'phone' => '+221770000006', 'role' => 'dahira_muqaddam', 'label' => 'Muqaddam', 'org' => $dahira],
            ['email' => 'secretaire.dahira@nujumalhuda.com', 'first_name' => 'Secrétaire', 'last_name' => 'Dahira', 'phone' => '+221770000007', 'role' => 'dahira_secretary', 'label' => 'Secrétaire du dahira', 'org' => $dahira],
            ['email' => 'tresorier@nujumalhuda.com', 'first_name' => 'Trésorier', 'last_name' => 'Nujum', 'phone' => '+221770000008', 'role' => 'dahira_treasurer', 'label' => 'Trésorier', 'org' => $dahira],
            ['email' => 'membre@nujumalhuda.com', 'first_name' => 'Membre', 'last_name' => 'Nujum', 'phone' => '+221770000009', 'role' => 'dahira_member', 'label' => 'Membre', 'org' => $dahira],
        ];

        foreach ($accounts as $account) {
            $role = Role::query()->firstOrCreate(['name' => $account['role']], [
                'display_name' => $account['label'],
                'description' => $account['label'],
                'is_system' => true,
            ]);

            $user = User::query()->firstOrNew(['email' => $account['email']]);
            if (!$user->exists) {
                $user->first_name = $account['first_name'];
                $user->last_name = $account['last_name'];
                $user->locale = 'fr';
                $user->timezone = 'Africa/Dakar';
            }
            $user->password = 'password';
            $user->email_verified_at ??= now();
            if (blank($user->phone) && !User::query()->where('phone', $account['phone'])->exists()) {
                $user->phone = $account['phone'];
            }
            $user->save();

            Membership::query()->firstOrCreate([
                'user_id' => $user->id,
                'organization_id' => $account['org']->id,
                'role_id' => $role->id,
            ], [
                'status' => MembershipStatus::ACTIVE,
                'joined_at' => now(),
            ]);

            if ($account['role'] === 'teacher') {
                Teacher::query()->firstOrCreate(['id' => $user->id], [
                    'organization_id' => $center->id,
                    'is_available' => true,
                    'is_featured' => false,
                    'bio_i18n' => [
                        'fr' => 'Compte de démonstration pour le rôle enseignant.',
                        'en' => 'Demonstration account for the teacher role.',
                        'ar' => 'حساب تجريبي لدور المدرّس.',
                    ],
                ]);
            }

            $this->command?->info("   {$account['label']} : {$user->email}");
        }
    }

    private function center(): Organization
    {
        $org = Organization::query()->firstOrNew(['slug' => 'nujum-al-huda']);
        if (!$org->exists) {
            $org->type = OrganizationType::CENTER;
            $org->email = 'contact@nujumalhuda.com';
            $org->country = 'SN';
            $org->city = 'Dakar';
            $org->is_active = true;
            $org->setTranslations('name', [
                'fr' => 'Nujum Al-Huda Institute Center',
                'en' => 'Nujum Al-Huda Institute Center',
                'ar' => 'مركز نجوم الهدى',
            ]);
            $org->save();
        }

        return $org;
    }

    private function dahira(): Organization
    {
        $org = Organization::query()->firstOrNew(['slug' => 'dahira-nujum-al-huda']);
        if (!$org->exists) {
            $org->type = OrganizationType::DAHIRA;
            $org->country = 'SN';
            $org->city = 'Dakar';
            $org->is_active = true;
            $org->setTranslations('name', [
                'fr' => 'Dahira Nujum Al-Huda',
                'en' => 'Nujum Al-Huda Dahira',
                'ar' => 'دائرة نجوم الهدى',
            ]);
            $org->save();
        }

        return $org;
    }
}
