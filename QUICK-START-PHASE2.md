# 🚀 Quick Start — Phase 2

**Modules implémentés** : Education, Mosque, News  
**Date** : 27 septembre 2026

---

## ⚡ Démarrage rapide

### 1. Installer les dépendances backend

```bash
cd backend
composer install
composer dump-autoload
```

### 2. Configuration

```bash
# Copier .env
cp .env.example .env

# Générer la clé Laravel
php artisan key:generate

# Configurer la base de données dans .env
DB_DATABASE=nujumalhuda
DB_USERNAME=nujumalhuda
DB_PASSWORD=CHANGE_ME
```

### 3. Lancer les migrations

```bash
# Migrations des 5 modules (Core, Announcements, Education, Mosque, News)
php artisan migrate

# Avec seeders (quand ils seront créés)
# php artisan migrate:fresh --seed
```

### 4. Vérifier les routes

```bash
# Lister toutes les routes API
php artisan route:list --path=api

# Filtrer par module
php artisan route:list --path=api/v1/education
php artisan route:list --path=api/v1/mosque
php artisan route:list --path=api/v1/news
```

### 5. Lancer le serveur de développement

```bash
# Backend
php artisan serve
# API disponible sur http://localhost:8000

# Frontend (dans un autre terminal)
cd ../frontend
npm install
npm run dev
# Frontend disponible sur http://localhost:3000
```

---

## 📋 Vérification des modules

### ✅ Module Education

**Routes à tester** :
```bash
# Liste des programmes
GET http://localhost:8000/api/v1/education/programs

# Détail d'un programme
GET http://localhost:8000/api/v1/education/programs/{id}

# Liste des promotions
GET http://localhost:8000/api/v1/education/promotions?open_only=1

# Liste des enseignants
GET http://localhost:8000/api/v1/education/teachers
```

### ✅ Module Mosque

**Routes à tester** :
```bash
# Horaires du jour
GET http://localhost:8000/api/v1/mosque/prayer-times

# Horaires d'une date spécifique
GET http://localhost:8000/api/v1/mosque/prayer-times?date=2026-09-28

# Liste des khutbas
GET http://localhost:8000/api/v1/mosque/khutbas

# Événements à venir
GET http://localhost:8000/api/v1/mosque/events
```

### ✅ Module News

**Routes à tester** :
```bash
# Liste des articles
GET http://localhost:8000/api/v1/news/articles

# Recherche d'articles
GET http://localhost:8000/api/v1/news/articles?search=coran

# Détail d'un article
GET http://localhost:8000/api/v1/news/articles/{slug}

# Liste des catégories
GET http://localhost:8000/api/v1/news/categories
```

---

## 🔧 Filament Admin

### Accéder à Filament

```
URL: http://localhost:8000/admin
```

**Note** : Les Filament Resources suivants sont disponibles :
- ✅ **Programs** (Education) : Complet avec pages CRUD
- ⏭️ **Promotions** (Education) : À créer
- ⏭️ **Enrollments** (Education) : À créer
- ⏭️ **Teachers** (Education) : À créer
- ⏭️ **Prayer Times** (Mosque) : À créer
- ⏭️ **Khutbas** (Mosque) : À créer
- ⏭️ **Events** (Mosque) : À créer
- ⏭️ **Articles** (News) : À créer
- ⏭️ **Categories** (News) : À créer
- ⏭️ **Comments** (News) : À créer

---

## 📊 Structure des modules

```
backend/modules/
├── Core/                      (16 migrations - Phase 1 ✅)
│   ├── Users, Organizations, Roles, Permissions
│   ├── Media, Notifications, Payments
│   └── Audit, Analytics, Settings
│
├── Announcements/             (3 migrations - Phase 1 ✅)
│   └── Bandeau temps réel via Reverb
│
├── Education/                 (8 migrations - Phase 2 ✅)
│   ├── Programs, Courses, Lessons
│   ├── Promotions, Enrollments
│   ├── Sessions, Attendances
│   └── Teachers
│
├── Mosque/                    (5 migrations - Phase 2 ✅)
│   ├── Prayer Times (auto + override)
│   ├── Iqama Adjustments
│   ├── Khutbas, Events
│   └── Mosque Announcements
│
└── News/                      (3 migrations - Phase 2 ✅)
    ├── Articles, Categories
    └── Comments (modérés)
```

**Total** : **35 migrations** (5 modules)

---

## 🐛 Troubleshooting

### Erreur : "Class 'Modules\...' not found"

```bash
cd backend
composer dump-autoload
```

### Erreur de migration

```bash
# Réinitialiser la base de données
php artisan migrate:fresh

# Avec données de demo (quand seeders créés)
php artisan migrate:fresh --seed
```

### Erreur Filament

```bash
# Réinstaller Filament
composer require filament/filament

# Publier les assets
php artisan filament:assets
```

### Vérifier les Service Providers

```bash
php artisan about

# Vérifier que les 5 modules sont enregistrés
```

---

## 📝 Données de test

### Créer un utilisateur admin

```bash
php artisan tinker

# Créer un admin
$user = \Modules\Core\Models\User::create([
    'id' => \Illuminate\Support\Str::ulid(),
    'name' => 'Admin Nujum Al-Huda',
    'email' => 'admin@nujumalhuda.com',
    'password' => bcrypt('password'),
    'email_verified_at' => now(),
]);
```

### Créer une organisation

```bash
php artisan tinker

$org = \Modules\Core\Models\Organization::create([
    'id' => \Illuminate\Support\Str::ulid(),
    'name' => 'Nujum Al-Huda Institute Center',
    'type' => 'institute',
    'slug' => 'nujum-al-huda',
    'is_active' => true,
]);
```

### Créer un programme de test

```bash
php artisan tinker

$program = \Modules\Education\Models\Program::create([
    'id' => \Illuminate\Support\Str::ulid(),
    'organization_id' => '01J...', // ID de l'org
    'name_i18n' => [
        'fr' => 'Mémorisation du Coran - Niveau débutant',
        'en' => 'Quran Memorization - Beginner Level',
        'ar' => 'حفظ القرآن الكريم - مستوى مبتدئ',
    ],
    'description_i18n' => [
        'fr' => 'Programme de mémorisation du Coran avec tajwid pour débutants',
        'en' => 'Quran memorization program with tajweed for beginners',
        'ar' => 'برنامج حفظ القرآن الكريم مع التجويد للمبتدئين',
    ],
    'type' => 'coran',
    'level' => 'debutant',
    'duration_weeks' => 52,
    'hours_per_week' => 10,
    'tuition_amount_minor' => 50000,
    'currency' => 'XOF',
    'is_active' => true,
]);
```

---

## 🎯 Prochaines étapes

### Backend
1. ✅ Migrations : **Terminé** (35 migrations)
2. ✅ Modèles : **Terminé** (21 modèles)
3. ✅ API Controllers : **Terminé** (13 contrôleurs)
4. ⏭️ **Filament Resources** : 10 à créer
5. ⏭️ **Seeders** : Données de démo
6. ⏭️ **Tests** : Feature tests API

### Frontend
1. ⏭️ Pages publiques : `/programs`, `/mosque`, `/news`
2. ⏭️ Formulaire d'inscription
3. ⏭️ Affichage horaires de prière
4. ⏭️ Blog trilingue
5. ⏭️ Intégration design system v2

### DevOps
1. ⏭️ Docker Compose production
2. ⏭️ Nginx configuration
3. ⏭️ SSL Let's Encrypt
4. ⏭️ Déploiement VPS

---

## 📚 Documentation

- [`docs/IMPLEMENTATION-PHASE2.md`](./docs/IMPLEMENTATION-PHASE2.md) : Documentation complète
- [`docs/DOMAIN-STRATEGY.md`](./docs/DOMAIN-STRATEGY.md) : Stratégie de domaine
- [`docs/design-system-v2.md`](./docs/design-system-v2.md) : Design system
- [`cahier-des-charges-nujum-al-huda.md`](./cahier-des-charges-nujum-al-huda.md) : Cahier des charges

---

## 💡 Commandes utiles

```bash
# Lister les modules
php artisan about

# Voir toutes les migrations
php artisan migrate:status

# Rollback dernière migration
php artisan migrate:rollback

# Créer un modèle
php artisan make:model Modules/Education/Models/MyModel

# Créer un contrôleur
php artisan make:controller Modules/Education/Http/Controllers/MyController

# Créer une migration
php artisan make:migration create_my_table --path=modules/Education/Database/Migrations

# Cache clear
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Optimize
php artisan optimize
```

---

## ✅ Checklist de démarrage

- [ ] Composer install
- [ ] .env configuré
- [ ] Base de données créée
- [ ] Migrations lancées
- [ ] Utilisateur admin créé
- [ ] Organisation créée
- [ ] Routes API testées
- [ ] Filament accessible
- [ ] Frontend lancé
- [ ] Données de test créées

---

**Nujum Al-Huda Institute Center**  
*Foi — Savoir — Éducation — Éthique — Excellence*

🌐 **[nujumalhuda.com](https://nujumalhuda.com)**
