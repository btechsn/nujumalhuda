# Script de génération des pages Filament

Ce fichier contient le code pour toutes les pages Filament manquantes.
Copier-coller dans les fichiers respectifs.

---

## Mosque - IqamaAdjustment (3 pages)

### ListIqamaAdjustments.php
```php
<?php
namespace Modules\Mosque\Filament\Resources\IqamaAdjustmentResource\Pages;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Modules\Mosque\Filament\Resources\IqamaAdjustmentResource;

class ListIqamaAdjustments extends ListRecords
{
    protected static string $resource = IqamaAdjustmentResource::class;
    protected function getHeaderActions(): array {
        return [Actions\CreateAction::make()];
    }
}
```

### CreateIqamaAdjustment.php
```php
<?php
namespace Modules\Mosque\Filament\Resources\IqamaAdjustmentResource\Pages;
use Filament\Resources\Pages\CreateRecord;
use Modules\Mosque\Filament\Resources\IqamaAdjustmentResource;

class CreateIqamaAdjustment extends CreateRecord
{
    protected static string $resource = IqamaAdjustmentResource::class;
}
```

### EditIqamaAdjustment.php
```php
<?php
namespace Modules\Mosque\Filament\Resources\IqamaAdjustmentResource\Pages;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Modules\Mosque\Filament\Resources\IqamaAdjustmentResource;

class EditIqamaAdjustment extends EditRecord
{
    protected static string $resource = IqamaAdjustmentResource::class;
    protected function getHeaderActions(): array {
        return [Actions\DeleteAction::make()];
    }
}
```

---

## Mosque - Khutba (3 pages)

### ListKhutbas.php, CreateKhutba.php, EditKhutba.php
(Même structure, remplacer IqamaAdjustment par Khutba)

---

## Mosque - Event (3 pages)

### ListEvents.php, CreateEvent.php, EditEvent.php
(Même structure, remplacer IqamaAdjustment par Event)

---

## News - Article (3 pages)

### ListArticles.php, CreateArticle.php, EditArticle.php
(Même structure, adapter namespace)

---

## News - Category (3 pages)

### ListCategories.php, CreateCategory.php, EditCategory.php
(Même structure, adapter namespace)

---

## News - Comment (2 pages, pas de Create)

### ListComments.php, EditComment.php
(Même structure, pas de CreateAction)

---

**Total : 17 pages à créer**
