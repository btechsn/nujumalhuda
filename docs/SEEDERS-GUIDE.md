# 🌱 Guide des Seeders — Données de démonstration

**Date** : 27 septembre 2026  
**Status** : ✅ Complet

---

## 📋 Vue d'ensemble

Les seeders génèrent des **données de test réalistes** pour l'institut Nujum Al-Huda :
- Organisation et utilisateur admin
- 5 programmes d'enseignement
- 4 enseignants avec bio complète
- 3 promotions (2 ouvertes, 1 en cours)
- 7 jours d'horaires de prière
- 5 khutbas du vendredi
- 3 événements à venir
- 4 catégories d'articles
- 4 articles de blog

**Total** : **36 entrées** de données réalistes

---

## 🚀 Utilisation rapide

### 1. Préparer la base de données

```bash
cd backend

# Installer les dépendances
composer install
composer dump-autoload

# Créer la base de données (si nécessaire)
php artisan migrate:fresh
```

### 2. Lancer tous les seeders

```bash
# Option 1 : Tout seeder d'un coup (RECOMMANDÉ)
php artisan db:seed

# Option 2 : Module par module
php artisan db:seed --class=Modules\\Education\\Database\\Seeders\\ProgramSeeder
php artisan db:seed --class=Modules\\Education\\Database\\Seeders\\TeacherSeeder
# ... etc
```

### 3. Accéder à l'admin

```
URL      : http://localhost:8000/admin
Email    : admin@nujumalhuda.com
Password : password
```

---

## 📦 Détail des seeders

### 🏢 Core (DatabaseSeeder)

**Fichier** : `backend/database/seeders/DatabaseSeeder.php`

**Contenu** :
- ✅ **1 Organisation** : Nujum Al-Huda Institute Center
  - ID fixe : `01HZXXX0000000000000000000`
  - Type : institute
  - Contact, adresse, réseaux sociaux
  
- ✅ **1 Utilisateur admin**
  - Email : `admin@nujumalhuda.com`
  - Password : `password`
  - Email vérifié

**⚠️ Important** : L'ID d'organisation `01HZXXX0000000000000000000` est utilisé partout. Si vous changez cet ID, mettez à jour tous les seeders.

---

### 🎓 Module Education

#### 1. ProgramSeeder

**Fichier** : `backend/modules/Education/Database/Seeders/ProgramSeeder.php`

**Contenu** : **5 programmes**

| Programme | Type | Niveau | Tarif | Durée |
|-----------|------|--------|-------|-------|
| Mémorisation du Coran | Coran | Débutant | 150 000 FCFA | 52 sem. |
| Langue Arabe | Arabe | Intermédiaire | 120 000 FCFA | 40 sem. |
| Œuvres de Cheikh Ibrahim Niasse | Baye Niasse | Avancé | 100 000 FCFA | 36 sem. |
| Sciences Islamiques - Fiqh et Hadith | Sunnite | Intermédiaire | 110 000 FCFA | 44 sem. |
| Initiation au Tajwid | Coran | Débutant | 60 000 FCFA | 20 sem. |

**Caractéristiques** :
- Noms trilingues (fr, en, ar)
- Descriptions détaillées
- Tarifs scolarité + inscription
- Âges min/max
- 3 programmes "featured"

#### 2. TeacherSeeder

**Fichier** : `backend/modules/Education/Database/Seeders/TeacherSeeder.php`

**Contenu** : **4 enseignants**

1. **Cheikh Abdoulaye Diop**
   - Email : a.diop@nujumalhuda.com
   - Spécialités : Tajwid, Mémorisation, Qira'at
   - Ijaza : ✅ Oui
   - 15 ans d'expérience
   - Diplômé Al-Azhar

2. **Ousmane Seck**
   - Email : o.seck@nujumalhuda.com
   - Spécialités : Grammaire arabe, Nahw, Sarf
   - Ijaza : ❌ Non
   - 10 ans d'expérience
   - Master langue arabe

3. **Serigne Fallou Mbacké**
   - Email : s.mbacke@nujumalhuda.com
   - Spécialités : Œuvres Baye Niasse, Soufisme, Fiqh
   - Ijaza : ✅ Oui
   - 20 ans d'expérience

4. **Aïcha Ndiaye**
   - Email : a.ndiaye@nujumalhuda.com
   - Spécialités : Enseignement enfants, Tajwid de base
   - Ijaza : ❌ Non
   - 8 ans d'expérience

**Caractéristiques** :
- Bio trilingue détaillée
- Qualifications et diplômes
- 2 avec ijaza + sanad
- Tous featured et disponibles

#### 3. PromotionSeeder

**Fichier** : `backend/modules/Education/Database/Seeders/PromotionSeeder.php`

**Contenu** : **3 promotions**

1. **Promotion Coran 2026-2027** (À venir, inscriptions ouvertes)
   - Code : CORAN-2026-A
   - Capacité : 30 élèves
   - Horaires : Lun/Mer/Ven 14h-16h
   - Enseignant : Cheikh Abdoulaye Diop

2. **Promotion Arabe 2026-2027** (À venir, inscriptions ouvertes)
   - Code : ARABE-2026-A
   - Capacité : 25 élèves
   - Horaires : Mar/Jeu 15h-17h
   - Enseignant : Ousmane Seck

3. **Promotion Fiqh 2026** (En cours, fermée)
   - Code : FIQH-2026-A
   - 15 inscrits / 20 places
   - Horaires : Sam 09h-12h
   - Enseignant : Serigne Fallou Mbacké

---

### 🕌 Module Mosque

#### 4. PrayerTimeSeeder

**Fichier** : `backend/modules/Mosque/Database/Seeders/PrayerTimeSeeder.php`

**Contenu** :
- ✅ **5 Décalages iqama** (standards par prière)
  - Fajr : 20 min
  - Dhuhr : 15 min
  - Asr : 15 min
  - Maghrib : 5 min
  - Isha : 15 min

- ✅ **35 Horaires de prière** (5 prières × 7 jours)
  - Horaires réalistes pour Dakar
  - Iqama calculée automatiquement
  - Méthode : MWL (Muslim World League)

**Horaires de base (Dakar)** :
```
Fajr    : 05:30 → Iqama 05:50
Dhuhr   : 13:15 → Iqama 13:30
Asr     : 16:45 → Iqama 17:00
Maghrib : 19:10 → Iqama 19:15
Isha    : 20:25 → Iqama 20:40
```

#### 5. KhutbaSeeder

**Fichier** : `backend/modules/Mosque/Database/Seeders/KhutbaSeeder.php`

**Contenu** : **5 khutbas**

1. La patience face aux épreuves (Vendredi dernier)
2. Les droits des parents en Islam (Il y a 1 semaine)
3. L'importance de la prière en commun (Il y a 2 semaines)
4. La sincérité dans les actes d'adoration (Il y a 3 semaines)
5. La gratitude envers Allah (Il y a 4 semaines)

**Caractéristiques** :
- Titres trilingues
- Résumés
- Points clés
- Références Coran/Hadith
- Toutes publiées

#### 6. EventSeeder

**Fichier** : `backend/modules/Mosque/Database/Seeders/EventSeeder.php`

**Contenu** : **3 événements**

1. **Conférence : Les valeurs de l'Islam**
   - Type : Conférence
   - Date : Dans 5 jours à 16h
   - Capacité : 100 personnes
   - Featured ✅

2. **Prière de Tarawih - Ramadan 2027**
   - Type : Prière spéciale
   - Récurrent : Oui (quotidien)
   - Date : Dans 7 mois
   - Featured ✅

3. **Collecte pour les orphelins**
   - Type : Fundraising
   - Date : Dans 10 jours
   - Objectif : 500 000 FCFA

---

### 📰 Module News

#### 7. CategorySeeder

**Fichier** : `backend/modules/News/Database/Seeders/CategorySeeder.php`

**Contenu** : **4 catégories**

1. **Vie du centre** (`vie-du-centre`)
2. **Enseignements** (`enseignements`)
3. **Communauté** (`communaute`)
4. **Événements** (`evenements`)

Toutes avec noms trilingues et descriptions.

#### 8. ArticleSeeder

**Fichier** : `backend/modules/News/Database/Seeders/ArticleSeeder.php`

**Contenu** : **4 articles**

1. **Rentrée 2026-2027 : Nouvelles promotions ouvertes**
   - Catégorie : Vie du centre
   - Featured ✅
   - Publié il y a 2 jours

2. **L'importance du Tajwid dans la récitation du Coran**
   - Catégorie : Enseignements
   - Publié il y a 5 jours

3. **Témoignage : Mon parcours à Nujum Al-Huda**
   - Catégorie : Communauté
   - Featured ✅
   - Publié il y a 7 jours

4. **Conférence sur les valeurs de l'Islam - 5 octobre**
   - Catégorie : Événements
   - Featured ✅
   - Publié il y a 1 jour

**Caractéristiques** :
- Contenu riche (HTML)
- Tags pertinents
- Vues aléatoires (50-300)
- Commentaires autorisés

---

## 🔧 Personnalisation

### Changer l'ID d'organisation

Si vous utilisez un ULID différent :

1. Créez votre organisation
2. Récupérez son ID
3. Remplacez `01HZXXX0000000000000000000` dans tous les seeders :

```bash
# PowerShell
Get-ChildItem -Recurse -Filter "*Seeder.php" | ForEach-Object {
    (Get-Content $_.FullName) -replace '01HZXXX0000000000000000000', 'VOTRE_ULID' | 
    Set-Content $_.FullName
}
```

### Ajouter plus de données

Dupliquez les entrées dans les arrays `$programs`, `$teachers`, etc.

### Modifier les horaires de prière

Éditez `PrayerTimeSeeder.php` :
```php
$prayerSchedule = [
    'fajr' => '05:30',    // Modifiez ici
    'dhuhr' => '13:15',
    // ...
];
```

---

## 🧪 Tests

### Vérifier les données

```bash
# Compter les entrées
php artisan tinker

>>> \Modules\Education\Models\Program::count()
=> 5

>>> \Modules\Education\Models\Teacher::count()
=> 4

>>> \Modules\Mosque\Models\PrayerTime::count()
=> 35

>>> \Modules\News\Models\Article::count()
=> 4
```

### Réinitialiser et re-seeder

```bash
php artisan migrate:fresh --seed
```

---

## 📊 Statistiques des seeders

```
Fichiers créés        : 10 seeders
Lignes de code        : ~1500 lignes
Données générées      : 36 entrées principales
Temps d'exécution     : ~5 secondes

Organisation          : 1
Utilisateurs          : 5 (1 admin + 4 enseignants)
Programmes            : 5
Promotions            : 3
Horaires prière       : 35 (7 jours × 5 prières)
Décalages iqama       : 5
Khutbas               : 5
Événements            : 3
Catégories articles   : 4
Articles              : 4
```

---

## ✅ Checklist après seeding

- [ ] Accéder à l'admin : http://localhost:8000/admin
- [ ] Vérifier les programmes (Éducation → Programmes)
- [ ] Vérifier les enseignants (Éducation → Enseignants)
- [ ] Vérifier les horaires de prière (Mosquée → Horaires)
- [ ] Vérifier les articles (Actualités → Articles)
- [ ] Tester l'API : GET http://localhost:8000/api/v1/education/programs
- [ ] Tester l'API : GET http://localhost:8000/api/v1/mosque/prayer-times

---

## 🎯 Prochaines étapes

Maintenant que les données de test sont prêtes :

1. **Tester l'admin Filament** : Créer, modifier, supprimer
2. **Tester les API** : Vérifier que les endpoints fonctionnent
3. **Commencer le frontend** : Pages avec vraies données
4. **Créer des inscriptions test** : Tester le workflow complet

---

**Seeders : ✅ 100% COMPLET**

*Nujum Al-Huda Institute Center — نجوم الهدى*
