<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Core\Enums\OrganizationType;
use Modules\Core\Models\Membership;
use Modules\Core\Models\Organization;
use Modules\Core\Models\Role;
use Modules\Core\Models\User;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('🌱 Démarrage du seeding...');

        // 1. Créer l'organisation principale
        $this->command->info('1️⃣  Création de l\'organisation...');
        
        $org = Organization::query()->firstOrNew(['slug' => 'nujum-al-huda']);
        $org->type = OrganizationType::CENTER;
        $org->email = 'contact@nujumalhuda.com';
        $org->phone = '+221771234567';
        $org->address = '28M Cité des Magistrats, Sud Foire, Dakar';
        $org->latitude = 14.7437965;
        $org->longitude = -17.4674915;
        $org->city = 'Dakar';
        $org->country = 'SN';
        $org->website = 'https://nujumalhuda.com';
        $org->is_active = true;
        $org->setTranslations('name', [
            'fr' => 'Nujum Al-Huda Institute Center',
            'en' => 'Nujum Al-Huda Institute Center',
            'ar' => 'مركز نجوم الهدى',
        ]);
        $org->save();

        $this->command->info("   ✅ Organisation créée : {$org->name}");

        $this->command->info('2️⃣  Création de l\'utilisateur admin...');

        $role = Role::query()->firstOrCreate(['name' => 'admin'], [
            'display_name' => 'Administrateur',
            'description' => 'Accès au panneau d\'administration',
            'is_system' => true,
        ]);

        $admin = User::query()->firstOrCreate(['email' => 'admin@nujumalhuda.com'], [
            'first_name' => 'Admin',
            'last_name' => 'Nujum Al-Huda',
            'password' => 'password',
            'locale' => 'fr',
            'timezone' => 'Africa/Dakar',
            'email_verified_at' => now(),
        ]);

        Membership::query()->firstOrCreate([
            'user_id' => $admin->id,
            'organization_id' => $org->id,
            'role_id' => $role->id,
        ], [
            'status' => 'active',
            'joined_at' => now(),
        ]);

        $this->command->info("   ✅ Admin créé : {$admin->email} / password: password");

        // 3. Module Education
        $this->command->info('3️⃣  Module Education...');
        $this->call(\Modules\Education\Database\Seeders\ProgramSeeder::class);
        $this->call(\Modules\Education\Database\Seeders\TeacherSeeder::class);
        $this->call(\Modules\Education\Database\Seeders\PromotionSeeder::class);

        // 4. Module Mosque
        $this->command->info('4️⃣  Module Mosque...');
        $this->call(\Modules\Mosque\Database\Seeders\PrayerTimeSeeder::class);
        $this->call(\Modules\Mosque\Database\Seeders\KhutbaSeeder::class);
        $this->call(\Modules\Mosque\Database\Seeders\EventSeeder::class);

        // 5. Module News
        $this->command->info('5️⃣  Module News...');
        $this->call(\Modules\News\Database\Seeders\CategorySeeder::class);
        $this->call(\Modules\News\Database\Seeders\ArticleSeeder::class);

        $this->command->info('6️⃣  Annonces...');
        $this->call(\Modules\Announcements\Database\Seeders\AnnouncementSeeder::class);

        $this->command->info('7️⃣  Module Academics (quiz + attestations)…');
        $this->call(\Modules\Academics\Database\Seeders\AcademicsSeeder::class);

        $this->command->info('8️⃣  Module Live + Replay…');
        $this->call(\Modules\Live\Database\Seeders\LiveStreamSeeder::class);
        $this->call(\Modules\Live\Database\Seeders\VodSeeder::class);
        $this->call(\Modules\Live\Database\Seeders\SocialAccountSeeder::class);

        $this->command->info('9️⃣  Module Resources (récitations)…');
        $this->call(\Modules\Resources\Database\Seeders\ResourcesSeeder::class);

        $this->command->info('🔟  Module Community + Dahira…');
        $this->call(\Modules\Community\Database\Seeders\CommunitySeeder::class);

        $this->command->newLine();
        $this->command->info('🎉 Seeding terminé avec succès !');
        $this->command->newLine();
        $this->command->info('📝 Identifiants de connexion :');
        $this->command->info("   Email    : admin@nujumalhuda.com");
        $this->command->info("   Password : password");
        $this->command->info("   URL Admin: http://localhost:8000/institut/administration");
        $this->command->newLine();
    }
}
