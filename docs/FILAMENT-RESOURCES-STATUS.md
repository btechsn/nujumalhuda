# 📊 État des Filament Resources

**Date** : 27 septembre 2026 22h38  
**Status Phase 2** : En cours

---

## ✅ Resources complètes (4/10)

### Module Education
1. ✅ **ProgramResource** — Gestion des programmes
   - Formulaire : nom trilingue, type, niveau, tarification, prérequis
   - Table : filtres par type/niveau, tri, badges
   - Actions : Edit, Delete
   - Pages : List, Create, Edit

2. ✅ **PromotionResource** — Gestion des promotions
   - Formulaire : programme, dates, capacité, enseignant, horaires
   - Table : filtres par statut, inscriptions ouvertes
   - Actions : Edit, Delete, Voir inscriptions
   - Pages : List, Create, Edit

3. ✅ **EnrollmentResource** — Validation des inscriptions
   - Formulaire : élève, promotion, contact d'urgence, paiement
   - Table : filtres par statut, promotion, paiement
   - Actions : Approuver, Refuser (avec raison), Edit, Delete
   - Pages : List, Create, Edit

4. ✅ **TeacherResource** — Fiches enseignants
   - Formulaire : bio trilingue, spécialités, qualifications, ijaza/sanad, disponibilité
   - Table : filtres par ijaza, disponibilité
   - Actions : Edit, Delete
   - Pages : List, Create, Edit

### Module Mosque
5. ✅ **PrayerTimeResource** — Override horaires de prière
   - Formulaire : date, prière, calculé (disabled), manuel (override), raison
   - Table : filtres par prière, date, override
   - Actions : Edit, Supprimer override (clear cache), Delete
   - Pages : List, Create, Edit
   - ⚠️ **Fonctionnalité critique** : Override manuel prioritaire

---

## ⏭️ Resources à créer (5/10)

### Module Mosque (3 resources)

6. **IqamaAdjustmentResource** — Décalages iqama
   ```php
   Formulaire :
   - Prière (fajr, dhuhr, asr, maghrib, isha)
   - Décalage en minutes (15 par défaut)
   - Période de validité (valid_from → valid_to)
   - Description (ex: "Horaire d'hiver")
   
   Table :
   - Colonnes : Prière, Décalage, Période, État
   - Filtres : Par prière, actifs uniquement
   - Actions : Edit, Delete, Activer/Désactiver
   ```

7. **KhutbaResource** — Gestion des khutbas
   ```php
   Formulaire :
   - Titre trilingue
   - Date et heure (vendredi)
   - Orateur (user ou nom externe)
   - Résumé et contenu trilingue (RichEditor)
   - Audio/Vidéo (select media)
   - YouTube URL
   - Références (versets, hadiths)
   - Publié (toggle)
   
   Table :
   - Colonnes : Date, Titre, Orateur, Publié
   - Filtres : Par date, publié
   - Actions : Edit, Delete, Publier/Dépublier
   ```

8. **EventResource** — Événements mosquée
   ```php
   Formulaire :
   - Titre et description trilingue
   - Type (lecture, conference, special_prayer, fundraising, community)
   - Dates (start_at, end_at)
   - Récurrent (is_recurring, pattern)
   - Lieu (location, details)
   - Orateur
   - Capacité et inscriptions (capacity, requires_registration)
   - Image de couverture
   - Mis en avant (is_featured)
   
   Table :
   - Colonnes : Titre, Type, Date, Capacité, Statut
   - Filtres : Par type, statut, mis en avant
   - Actions : Edit, Delete
   ```

### Module News (3 resources)

9. **ArticleResource** — Rédaction d'articles
   ```php
   Formulaire :
   - Titre trilingue
   - Slug (généré auto depuis titre FR)
   - Catégorie (select)
   - Résumé trilingue (Textarea)
   - Contenu trilingue (RichEditor)
   - Image de couverture (select media)
   - Tags (TagsInput)
   - Meta description & keywords
   - Statut (draft, published, archived)
   - Date de publication
   - Options : is_featured, allow_comments
   
   Table :
   - Colonnes : Titre, Catégorie, Auteur, Statut, Vues, Commentaires, Publié le
   - Filtres : Par catégorie, statut, auteur, featured
   - Actions : Edit, Delete, Publier/Dépublier
   ```

10. **CategoryResource** — Catégories d'articles
    ```php
    Formulaire :
    - Nom trilingue
    - Slug (généré auto)
    - Description trilingue (optional)
    - Ordre d'affichage
    - Actif (toggle)
    
    Table :
    - Colonnes : Nom, Slug, Nombre d'articles, Ordre, Actif
    - Filtres : Actifs uniquement
    - Actions : Edit, Delete
    ```

11. **CommentResource** — Modération commentaires
    ```php
    Formulaire :
    - Article (select, disabled)
    - Utilisateur (select, disabled)
    - Contenu (Textarea, disabled)
    - Statut (pending, approved, rejected, flagged)
    - Raison de modération (Textarea)
    
    Table :
    - Colonnes : Article, Utilisateur, Contenu (excerpt), Statut, Date
    - Filtres : Par statut, article, utilisateur
    - Actions : Approuver, Refuser (avec raison), Edit, Delete
    - Badge "En attente" (count pending dans navigation)
    ```

---

## 📝 Template pour créer une page Filament standard

### ListPage.php
```php
<?php

namespace Modules\[Module]\Filament\Resources\[Resource]Resource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Modules\[Module]\Filament\Resources\[Resource]Resource;

class List[Resource]s extends ListRecords
{
    protected static string $resource = [Resource]Resource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
```

### CreatePage.php
```php
<?php

namespace Modules\[Module]\Filament\Resources\[Resource]Resource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\[Module]\Filament\Resources\[Resource]Resource;

class Create[Resource] extends CreateRecord
{
    protected static string $resource = [Resource]Resource::class;
}
```

### EditPage.php
```php
<?php

namespace Modules\[Module]\Filament\Resources\[Resource]Resource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Modules\[Module]\Filament\Resources\[Resource]Resource;

class Edit[Resource] extends EditRecord
{
    protected static string $resource = [Resource]Resource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
```

---

## 🚀 Commandes pour générer rapidement

```bash
# Générer un Filament Resource
php artisan make:filament-resource [Module]/[Model] --generate

# Ou créer manuellement selon le template ci-dessus
```

---

## 🎯 Priorités

### Haute priorité ⚡
1. **CommentResource** (News) — Modération critique
2. **ArticleResource** (News) — Création de contenu
3. **IqamaAdjustmentResource** (Mosque) — Complète PrayerTime

### Priorité moyenne 🔸
4. **KhutbaResource** (Mosque) — Archivage khutbas
5. **EventResource** (Mosque) — Communication événements
6. **CategoryResource** (News) — Organisation articles

---

## 📊 Statistiques

```
Filament Resources créés : 5/11 (45%)
Pages Filament créées    : 15/33 (45%)
Temps estimé restant     : ~2h pour les 6 resources manquants
```

---

## ✅ Pour compléter

1. Créer les 6 Filament Resources manquants selon les specs ci-dessus
2. Créer les 3 pages standards pour chaque (List, Create, Edit)
3. Tester l'admin Filament : http://localhost:8000/admin
4. Créer des seeders pour tester avec données réelles

---

**Dernière mise à jour** : 27 septembre 2026, 22h38  
**Status** : 5 resources complètes, 6 à créer
