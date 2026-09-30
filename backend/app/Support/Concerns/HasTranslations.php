<?php

declare(strict_types=1);

namespace App\Support\Concerns;

use Illuminate\Support\Facades\App;

/**
 * Trait pour les attributs multilingues stockés en JSONB.
 *
 * Structure attendue : {"fr": "...", "en": "...", "ar": "..."}
 *
 * Usage :
 *   protected array $translatable = ['title', 'description'];
 *
 *   $model->title; // Retourne la traduction dans la locale courante
 *   $model->getTranslation('title', 'ar'); // Force une locale
 *   $model->setTranslation('title', 'fr', 'Nouveau titre');
 */
trait HasTranslations
{
    /**
     * Locales supportées.
     *
     * La classe qui utilise ce trait déclare :
     *   protected array $translatable = ['title', 'description'];
     */
    protected array $supportedLocales = ['fr', 'en', 'ar'];

    /**
     * Locale de repli.
     */
    protected string $fallbackLocale = 'fr';

    /**
     * Initialise le trait.
     */
    public function initializeHasTranslations(): void
    {
        $this->casts = array_merge(
            $this->casts ?? [],
            array_fill_keys($this->translatable, 'array')
        );
    }

    /**
     * Récupère un attribut traduit.
     */
    public function getAttributeValue($key): mixed
    {
        $value = parent::getAttributeValue($key);

        if (in_array($key, $this->translatable) && is_array($value)) {
            return $this->getTranslation($key);
        }

        return $value;
    }

    /**
     * Récupère une traduction spécifique.
     */
    public function getTranslation(string $attribute, ?string $locale = null): ?string
    {
        $locale = $locale ?? App::getLocale();
        $translations = $this->attributes[$attribute] ?? null;

        if (is_string($translations)) {
            $translations = json_decode($translations, true);
        }

        if (!is_array($translations)) {
            return null;
        }

        // Locale demandée
        if (isset($translations[$locale])) {
            return $translations[$locale];
        }

        // Repli sur le français
        if (isset($translations[$this->fallbackLocale])) {
            return $translations[$this->fallbackLocale];
        }

        // Première traduction disponible
        return !empty($translations) ? reset($translations) : null;
    }

    /**
     * Définit une traduction.
     */
    public function setTranslation(string $attribute, string $locale, ?string $value): self
    {
        $translations = $this->attributes[$attribute] ?? null;

        if (is_string($translations)) {
            $translations = json_decode($translations, true);
        }

        if (!is_array($translations)) {
            $translations = [];
        }

        $translations[$locale] = $value;

        $this->attributes[$attribute] = json_encode($translations);

        return $this;
    }

    /**
     * Définit toutes les traductions d'un attribut.
     */
    public function setTranslations(string $attribute, array $translations): self
    {
        $this->attributes[$attribute] = json_encode($translations);

        return $this;
    }

    /**
     * Récupère toutes les traductions d'un attribut.
     */
    public function getTranslations(string $attribute): array
    {
        $translations = $this->attributes[$attribute] ?? null;

        if (is_string($translations)) {
            $translations = json_decode($translations, true);
        }

        return is_array($translations) ? $translations : [];
    }

    /**
     * Vérifie si une traduction existe.
     */
    public function hasTranslation(string $attribute, string $locale): bool
    {
        $translations = $this->getTranslations($attribute);

        return isset($translations[$locale]) && !empty($translations[$locale]);
    }
}
