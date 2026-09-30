<?php

declare(strict_types=1);

namespace Modules\Core\Contracts;

use Illuminate\Http\UploadedFile;

/**
 * Interface pour le stockage et la transformation des médias.
 */
interface MediaStorage
{
    /**
     * Stocke un fichier et retourne son chemin.
     */
    public function store(UploadedFile $file, string $path): string;

    /**
     * Supprime un fichier.
     */
    public function delete(string $path): bool;

    /**
     * Retourne l'URL publique d'un fichier.
     */
    public function url(string $path): string;

    /**
     * Génère une variante (thumbnail, optimisée, etc.).
     */
    public function createVariant(string $path, string $variant, array $options = []): string;
}
