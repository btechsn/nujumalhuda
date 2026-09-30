<?php

namespace Modules\Dahira\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Core\Enums\OrganizationType;
use Modules\Core\Models\Organization;
use Modules\Core\Models\Role;
use Modules\Dahira\Models\ContributionPlan;
use Modules\Dahira\Models\DahiraGroup;
use Modules\Dahira\Models\Meeting;

class DahiraSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            'dahira_muqaddam' => 'Muqaddam',
            'dahira_secretary' => 'Secrétaire',
            'dahira_treasurer' => 'Trésorier',
            'dahira_member' => 'Membre',
        ] as $name => $label) {
            Role::firstOrCreate(['name' => $name], [
                'display_name' => $label,
                'description' => 'Rôle interne d\'un dahira',
                'is_system' => true,
            ]);
        }

        $organization = Organization::query()->where('slug', 'dahira-nujum-al-huda')->first() ?? new Organization();
        $organization->type = OrganizationType::DAHIRA;
        $organization->slug = 'dahira-nujum-al-huda';
        $organization->country = 'SN';
        $organization->city = 'Dakar';
        $organization->is_active = true;
        $organization->setTranslations('name', [
            'fr' => 'Dahira Nujum Al-Huda',
            'en' => 'Nujum Al-Huda Dahira',
            'ar' => 'دائرة نجوم الهدى',
        ]);
        $organization->save();

        $group = DahiraGroup::updateOrCreate(
            ['organization_id' => $organization->id],
            [
                'name_i18n' => [
                    'fr' => 'Dahira Nujum Al-Huda',
                    'en' => 'Nujum Al-Huda Dahira',
                    'ar' => 'دائرة نجوم الهدى',
                ],
                'description_i18n' => [
                    'fr' => 'Cercle principal du centre : dhikr, entraide et service à la zawiya.',
                    'en' => 'Main circle of the centre: dhikr, mutual aid and service at the zawiya.',
                    'ar' => 'الحلقة الرئيسية للمركز: ذكر وتعاون وخدمة في الزاوية.',
                ],
                'meeting_weekday' => 5,
                'meeting_time' => '16:00:00',
                'location' => 'Zawiya Nujum Al-Huda, Sud Foire',
                'is_active' => true,
            ]
        );

        ContributionPlan::updateOrCreate(
            ['dahira_group_id' => $group->id, 'frequency' => 'monthly'],
            [
                'name_i18n' => ['fr' => 'Cotisation mensuelle', 'en' => 'Monthly contribution', 'ar' => 'الاشتراك الشهري'],
                'amount_minor' => 2000,
                'currency' => 'XOF',
                'due_day' => 5,
                'is_active' => true,
            ]
        );

        $meetings = [
            [
                'title' => 'Réunion hebdomadaire du dahira',
                'agenda' => 'Dhikr, rappel et points d’organisation du cercle.',
                'starts_at' => now()->next('Friday')->setTime(16, 0),
                'location' => 'Zawiya Nujum Al-Huda',
            ],
            [
                'title' => 'Préparation du service communautaire',
                'agenda' => 'Organisation de l’entraide et des actions du mois.',
                'starts_at' => now()->next('Friday')->addWeek()->setTime(16, 0),
                'location' => 'Zawiya Nujum Al-Huda',
            ],
            [
                'title' => 'Majlis des membres',
                'agenda' => 'Bilan des cotisations et projets du dahira.',
                'starts_at' => now()->next('Friday')->addWeeks(2)->setTime(16, 30),
                'location' => 'Institut Nujum Al-Huda, Sud Foire',
            ],
        ];

        foreach ($meetings as $row) {
            Meeting::updateOrCreate(
                [
                    'dahira_group_id' => $group->id,
                    'title' => $row['title'],
                ],
                [
                    'starts_at' => $row['starts_at'],
                    'location' => $row['location'],
                    'agenda' => $row['agenda'],
                    'convened_at' => null,
                ]
            );
        }
    }
}
