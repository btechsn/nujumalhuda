# Script de génération automatique des pages Filament
# Usage: .\generate-filament-pages.ps1

$pages = @(
    @{Module="Mosque"; Resource="IqamaAdjustment"; Pages=@("List", "Create", "Edit")},
    @{Module="Mosque"; Resource="Khutba"; Pages=@("List", "Create", "Edit")},
    @{Module="Mosque"; Resource="Event"; Pages=@("List", "Create", "Edit")},
    @{Module="News"; Resource="Article"; Pages=@("List", "Create", "Edit")},
    @{Module="News"; Resource="Category"; Pages=@("List", "Create", "Edit")},
    @{Module="News"; Resource="Comment"; Pages=@("List", "Edit")}
)

foreach ($config in $pages) {
    $module = $config.Module
    $resource = $config.Resource
    $basePath = "backend\modules\$module\Filament\Resources\${resource}Resource\Pages"
    
    foreach ($page in $config.Pages) {
        $filename = "$basePath\$page${resource}s.php"
        if ($page -eq "List") { $filename = "$basePath\List${resource}s.php" }
        elseif ($page -eq "Create") { $filename = "$basePath\Create${resource}.php" }
        else { $filename = "$basePath\Edit${resource}.php" }
        
        $content = @"
<?php

namespace Modules\$module\Filament\Resources\${resource}Resource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\$(if ($page -eq "List") { "ListRecords" } elseif ($page -eq "Create") { "CreateRecord" } else { "EditRecord" });
use Modules\$module\Filament\Resources\${resource}Resource;

class $page${resource}$(if ($page -eq "List") { "s" } else { "" }) extends $(if ($page -eq "List") { "ListRecords" } elseif ($page -eq "Create") { "CreateRecord" } else { "EditRecord" })
{
    protected static string `$resource = ${resource}Resource::class;
$(if ($page -ne "Create") {
"
    protected function getHeaderActions(): array
    {
        return [
$(if ($page -eq "List") { "            Actions\CreateAction::make()," } else { "            Actions\DeleteAction::make()," })
        ];
    }"
})
}
"@
        
        Write-Host "Création de $filename"
        $content | Out-File -FilePath $filename -Encoding UTF8
    }
}

Write-Host "`n✅ Toutes les pages Filament ont été créées !"
