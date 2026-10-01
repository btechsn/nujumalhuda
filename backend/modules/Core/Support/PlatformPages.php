<?php

declare(strict_types=1);

namespace Modules\Core\Support;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Modules\Core\Models\Setting;
use Throwable;

/**
 * Textes éditables de chaque page publique.
 * Les valeurs enregistrées remplacent les fichiers de traduction du site.
 */
final class PlatformPages
{
    /**
     * @var array<string, array<string, mixed>>
     */
    private static array $messages = [];

    /**
     * @return array<int, array{id: string, label: string, sort: int, slug: string, root: string, class: class-string, navigation: bool}>
     */
    public static function definitions(): array
    {
        return [
            self::page('centre', 'Centre', 10, 'pages/centre', 'doors.centre', \App\Filament\Pages\Content\CentrePage::class),
            self::page('zawiya', 'Zawiya', 20, 'pages/zawiya', 'pages.zawiya', \App\Filament\Pages\Content\ZawiyaPage::class),
            self::page('prayers', 'Horaires de prière', 30, 'pages/horaires', 'pages.prayers', \App\Filament\Pages\Content\PrayerTimesPage::class, false),
            self::page('calendar', 'Calendrier hégirien', 40, 'pages/calendrier', 'pages.calendar', \App\Filament\Pages\Content\CalendarPage::class, false),
            self::page('khutbas', 'Khutbas', 50, 'pages/khutbas', 'pages.khutbas', \App\Filament\Pages\Content\KhutbasPage::class, false),
            self::page('events', 'Événements', 60, 'pages/evenements', 'pages.events', \App\Filament\Pages\Content\EventsPage::class),
            self::page('announcements', 'Annonces', 70, 'pages/annonces', 'pages.announcements', \App\Filament\Pages\Content\AnnouncementsPage::class),
            self::page('programs', 'Programmes', 80, 'pages/programmes', 'pages.programs', \App\Filament\Pages\Content\ProgramsPage::class),
            self::page('teachers', 'Enseignants', 90, 'pages/enseignants', 'pages.teachers', \App\Filament\Pages\Content\TeachersPage::class),
            self::page('enroll', 'Inscription', 100, 'pages/inscription', 'pages.enroll', \App\Filament\Pages\Content\EnrollmentPage::class),
            self::page('promotions', 'Promotions', 110, 'pages/promotions', 'pages.promotions', \App\Filament\Pages\Content\PromotionsPage::class),
            self::page('quiz', 'Quizz', 120, 'pages/quizz', 'pages.quiz', \App\Filament\Pages\Content\QuizPage::class),
            self::page('certificates', 'Certificats', 130, 'pages/certificats', 'pages.certificates', \App\Filament\Pages\Content\CertificatesPage::class),
            self::page('ijaza', 'Ijaza', 140, 'pages/ijaza', 'pages.ijaza', \App\Filament\Pages\Content\IjazaPage::class),
            self::page('news', 'Actualités', 150, 'pages/actualites', 'news', \App\Filament\Pages\Content\NewsPage::class),
            self::page('live', 'En direct', 160, 'pages/direct', 'pages.live', \App\Filament\Pages\Content\LivePage::class),
            self::page('replay', 'Replay', 170, 'pages/replay', 'pages.replay', \App\Filament\Pages\Content\ReplayPage::class),
            self::page('recitations', 'Récitations', 180, 'pages/recitations', 'pages.recitations', \App\Filament\Pages\Content\RecitationsPage::class),
            self::page('library', 'Bibliothèque', 190, 'pages/bibliotheque', 'pages.library', \App\Filament\Pages\Content\LibraryPage::class),
            self::page('media', 'Médiathèque', 200, 'pages/mediatheque', 'pages.media', \App\Filament\Pages\Content\MediaPage::class),
            self::page('daily', 'Du jour', 210, 'pages/du-jour', 'pages.daily', \App\Filament\Pages\Content\DailyPage::class),
            self::page('zakat', 'Zakat', 220, 'pages/zakat', 'pages.zakat', \App\Filament\Pages\Content\ZakatPage::class),
            self::page('muud', 'Muud Ramadan', 230, 'pages/muud-ramadan', 'pages.muud', \App\Filament\Pages\Content\MuudPage::class),
            self::page('dahira', 'Dahiras', 240, 'pages/dahiras', 'pages.dahira', \App\Filament\Pages\Content\DahiraPage::class),
            self::page('community', 'Communauté', 250, 'pages/communaute', 'pages.community', \App\Filament\Pages\Content\CommunityPage::class),
            self::page('discussions', 'Discussions', 260, 'pages/discussions', 'pages.discussions', \App\Filament\Pages\Content\DiscussionsPage::class),
            self::page('contact', 'Contact', 270, 'pages/contact', 'pages.contact', \App\Filament\Pages\Content\ContactPage::class),
        ];
    }

    /**
     * @return array<int, class-string>
     */
    public static function pageClasses(): array
    {
        $pages = array_filter(
            self::definitions(),
            static fn (array $definition): bool => $definition['navigation'],
        );

        return array_column($pages, 'class');
    }

    /**
     * @return array{id: string, label: string, sort: int, slug: string, root: string, class: class-string}
     */
    public static function definition(string $id): array
    {
        foreach (self::definitions() as $definition) {
            if ($definition['id'] === $id) {
                return $definition;
            }
        }

        throw new \InvalidArgumentException("Page inconnue : {$id}");
    }

    /**
     * @return array<int, array{name: string, path: string, label: string, type: string}>
     */
    public static function fields(string $id): array
    {
        $definition = self::definition($id);
        $node = data_get(self::messages('fr'), $definition['root']);
        $fields = [];

        if ($id === 'centre') {
            $fields[] = [
                'name' => 'image',
                'path' => '',
                'label' => 'Photo',
                'type' => 'image',
                'directory' => 'pages/centre',
            ];
        }

        if ($id === 'zawiya') {
            $fields[] = [
                'name' => 'founder_image',
                'path' => '',
                'label' => 'Photo du fondateur',
                'type' => 'image',
                'directory' => 'pages/zawiya',
            ];
        }

        if (is_array($node)) {
            foreach (self::labels() as $name => $label) {
                if (! isset($node[$name]) || ! is_string($node[$name])) {
                    continue;
                }

                $fields[] = self::textField($name, $definition['root'].'.'.$name, $label);
            }
        }

        foreach (self::extraFields($id) as $field) {
            $fields[] = $field;
        }

        if (is_array($node) && isset($node['points']) && is_array($node['points'])) {
            $first = $node['points'][0] ?? null;

            if ($first === null || is_string($first)) {
                $fields[] = [
                    'name' => 'points',
                    'path' => $definition['root'].'.points',
                    'label' => 'Points',
                    'type' => 'points',
                ];
            }
        }

        return $fields;
    }

    /**
     * @return array<string, mixed>
     */
    public static function formState(string $id): array
    {
        $stored = self::stored($id);
        $state = [];

        foreach (self::fields($id) as $field) {
            if ($field['type'] === 'image') {
                $state[$field['name']] = self::imagePath($stored[$field['name']] ?? null);

                continue;
            }

            if ($field['type'] === 'points') {
                $state['points'] = isset($stored['points']) && is_array($stored['points'])
                    ? self::normalizePoints($stored['points'])
                    : self::defaultPoints($field['path']);

                continue;
            }

            $source = isset($field['store']) ? self::stored((string) $field['store']) : $stored;
            $key = (string) ($field['storeKey'] ?? $field['name']);
            $row = $source[$key] ?? null;
            $state[$field['name']] = [
                'fr' => is_array($row) ? (string) ($row['fr'] ?? '') : self::defaultString($field['path'], 'fr'),
                'en' => is_array($row) ? (string) ($row['en'] ?? '') : self::defaultString($field['path'], 'en'),
                'ar' => is_array($row) ? (string) ($row['ar'] ?? '') : self::defaultString($field['path'], 'ar'),
            ];

            if (! is_array($row)) {
                continue;
            }

            foreach (['fr', 'en', 'ar'] as $locale) {
                if ($state[$field['name']][$locale] === '') {
                    $state[$field['name']][$locale] = self::defaultString($field['path'], $locale);
                }
            }
        }

        return $state;
    }

    /**
     * @param  array<string, mixed>  $state
     */
    public static function persist(string $id, array $state): void
    {
        $payload = [];
        $linked = [];

        foreach (self::fields($id) as $field) {
            if ($field['type'] === 'image') {
                $payload[$field['name']] = self::imagePath($state[$field['name']] ?? null);

                continue;
            }

            if ($field['type'] === 'points') {
                $payload['points'] = self::normalizePoints($state['points'] ?? []);

                continue;
            }

            $row = is_array($state[$field['name']] ?? null) ? $state[$field['name']] : [];
            $trio = [
                'fr' => trim((string) ($row['fr'] ?? '')),
                'en' => trim((string) ($row['en'] ?? '')),
                'ar' => trim((string) ($row['ar'] ?? '')),
            ];

            if (isset($field['store'], $field['storeKey'])) {
                $linked[(string) $field['store']][(string) $field['storeKey']] = $trio;

                continue;
            }

            $payload[$field['name']] = $trio;
        }

        self::writePage($id, $payload);

        foreach ($linked as $storeId => $patch) {
            self::writePage($storeId, array_replace(self::stored($storeId), $patch));
        }
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private static function writePage(string $id, array $payload): void
    {
        Setting::query()->updateOrCreate(
            ['key' => 'page.'.$id],
            [
                'value' => json_encode($payload, JSON_UNESCAPED_UNICODE),
                'type' => 'json',
                'group' => 'pages',
                'description' => self::definition($id)['label'],
                'is_public' => true,
            ],
        );
    }

    /**
     * Arbre de textes à fusionner dans les traductions du site.
     *
     * @return array<string, mixed>
     */
    public static function publicCopy(): array
    {
        if (! self::tableExists()) {
            return [];
        }

        try {
            $rows = Setting::query()->where('group', 'pages')->get(['key', 'value']);
        } catch (Throwable) {
            return [];
        }

        $tree = [];

        foreach ($rows as $row) {
            $id = str_starts_with((string) $row->key, 'page.') ? substr((string) $row->key, 5) : '';

            if ($id === '' || ! self::known($id)) {
                continue;
            }

            $stored = json_decode((string) $row->value, true);

            if (! is_array($stored)) {
                continue;
            }

            foreach (self::fields($id) as $field) {
                if ($field['type'] === 'image') {
                    continue;
                }

                if ($field['type'] === 'points') {
                    $points = self::normalizePoints($stored['points'] ?? []);

                    if ($points !== []) {
                        data_set($tree, $field['path'], $points);
                    }

                    continue;
                }

                $value = $stored[$field['name']] ?? null;

                if (! is_array($value)) {
                    continue;
                }

                data_set($tree, $field['path'], [
                    'fr' => trim((string) ($value['fr'] ?? '')),
                    'en' => trim((string) ($value['en'] ?? '')),
                    'ar' => trim((string) ($value['ar'] ?? '')),
                ]);
            }
        }

        return $tree;
    }

    public static function imageUrl(string $id, string $name = 'image'): ?string
    {
        $path = self::imagePath(self::stored($id)[$name] ?? null);

        if ($path === null) {
            return null;
        }

        return Storage::disk('public')->url($path);
    }

    private static function imagePath(mixed $value): ?string
    {
        if (is_array($value)) {
            $value = reset($value);
        }

        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }

    /**
     * @param  array<string, mixed>  $points
     * @return array<int, array{fr: string, en: string, ar: string}>
     */
    private static function normalizePoints(array $points): array
    {
        $rows = [];

        foreach ($points as $point) {
            if (! is_array($point)) {
                continue;
            }

            $row = [
                'fr' => trim((string) ($point['fr'] ?? '')),
                'en' => trim((string) ($point['en'] ?? '')),
                'ar' => trim((string) ($point['ar'] ?? '')),
            ];

            if ($row['fr'] === '' && $row['en'] === '' && $row['ar'] === '') {
                continue;
            }

            $rows[] = $row;
        }

        return $rows;
    }

    /**
     * @return array<int, array{fr: string, en: string, ar: string}>
     */
    private static function defaultPoints(string $path): array
    {
        $byLocale = [];

        foreach (['fr', 'en', 'ar'] as $locale) {
            $value = data_get(self::messages($locale), $path);
            $byLocale[$locale] = is_array($value) ? array_values($value) : [];
        }

        $count = max(count($byLocale['fr']), count($byLocale['en']), count($byLocale['ar']));
        $rows = [];

        for ($index = 0; $index < $count; $index++) {
            $rows[] = [
                'fr' => is_string($byLocale['fr'][$index] ?? null) ? $byLocale['fr'][$index] : '',
                'en' => is_string($byLocale['en'][$index] ?? null) ? $byLocale['en'][$index] : '',
                'ar' => is_string($byLocale['ar'][$index] ?? null) ? $byLocale['ar'][$index] : '',
            ];
        }

        return $rows;
    }

    private static function defaultString(string $path, string $locale): string
    {
        $value = data_get(self::messages($locale), $path);

        return is_string($value) ? $value : '';
    }

    /**
     * @return array<string, mixed>
     */
    private static function stored(string $id): array
    {
        if (! self::tableExists()) {
            return [];
        }

        try {
            $value = Setting::query()->where('key', 'page.'.$id)->value('value');
        } catch (Throwable) {
            return [];
        }

        if (! is_string($value) || $value === '') {
            return [];
        }

        $decoded = json_decode($value, true);

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * @return array<string, mixed>
     */
    private static function messages(string $locale): array
    {
        if (isset(self::$messages[$locale])) {
            return self::$messages[$locale];
        }

        $path = dirname(base_path()).DIRECTORY_SEPARATOR.'frontend'.DIRECTORY_SEPARATOR.'messages'.DIRECTORY_SEPARATOR.$locale.'.json';

        if (! is_file($path)) {
            return self::$messages[$locale] = [];
        }

        try {
            $decoded = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
        } catch (Throwable) {
            return self::$messages[$locale] = [];
        }

        return self::$messages[$locale] = is_array($decoded) ? $decoded : [];
    }

    private static function known(string $id): bool
    {
        foreach (self::definitions() as $definition) {
            if ($definition['id'] === $id) {
                return true;
            }
        }

        return false;
    }

    private static function tableExists(): bool
    {
        try {
            return Schema::hasTable('settings');
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * @return array<string, string>
     */
    private static function labels(): array
    {
        return [
            'eyebrow' => 'Surtitre',
            'title' => 'Titre',
            'lede' => 'Introduction',
            'about' => 'Présentation',
            'founderEyebrow' => 'Surtitre du fondateur',
            'founderTitle' => 'Titre du fondateur',
            'founderName' => 'Nom du fondateur',
            'founderP1' => 'Paragraphe 1',
            'founderP2' => 'Paragraphe 2',
            'founderP3' => 'Paragraphe 3',
            'placeText' => 'Le lieu',
            'languagesText' => 'Les langues',
            'teachText' => 'Ce qui s’enseigne',
            'testimonialsTitle' => 'Titre des témoignages',
            'testimonialsLede' => 'Texte des témoignages',
            'partnersTitle' => 'Titre des partenaires',
            'partnersLede' => 'Texte des partenaires',
            'missionsTitle' => 'Titre des missions',
            'missionsLede' => 'Texte des missions',
        ];
    }

    /**
     * @return array<int, array{name: string, path: string, label: string, type: string}>
     */
    private static function extraFields(string $id): array
    {
        if ($id === 'zawiya') {
            return [
                self::linkedField('prayers', 'lede', 'Horaires — introduction', 'textarea'),
                self::linkedField('khutbas', 'title', 'Khutbas — titre', 'text'),
                self::linkedField('khutbas', 'lede', 'Khutbas — introduction', 'textarea'),
                self::linkedField('calendar', 'title', 'Calendrier — titre', 'text'),
                self::linkedField('calendar', 'lede', 'Calendrier — introduction', 'textarea'),
            ];
        }

        if ($id !== 'centre') {
            return [];
        }

        $missions = [
            'centre' => 'Le centre',
            'zawiya' => 'La zawiya',
            'academics' => 'L’académique',
            'community' => 'La communauté',
            'live' => 'La diffusion',
            'welcome' => 'L’accueil',
        ];
        $fields = [];

        foreach ($missions as $key => $label) {
            $fields[] = self::textField(
                'mission_'.$key.'_title',
                'doors.centre.missions.'.$key.'.title',
                'Mission — '.$label.' (titre)',
            );
            $fields[] = self::textField(
                'mission_'.$key.'_text',
                'doors.centre.missions.'.$key.'.text',
                'Mission — '.$label.' (texte)',
            );
        }

        return $fields;
    }

    /**
     * @return array{name: string, path: string, label: string, type: string, store: string, storeKey: string}
     */
    private static function linkedField(string $store, string $key, string $label, string $type): array
    {
        return [
            'name' => $store.'_'.$key,
            'path' => 'pages.'.$store.'.'.$key,
            'label' => $label,
            'type' => $type,
            'store' => $store,
            'storeKey' => $key,
        ];
    }

    /**
     * @return array{name: string, path: string, label: string, type: string}
     */
    private static function textField(string $name, string $path, string $label): array
    {
        $long = in_array($name, ['lede', 'about'], true)
            || str_starts_with($name, 'founderP')
            || str_ends_with($name, 'Text')
            || str_ends_with($name, 'Lede')
            || str_ends_with($name, '_text');

        return [
            'name' => $name,
            'path' => $path,
            'label' => $label,
            'type' => $long ? 'textarea' : 'text',
        ];
    }

    /**
     * @param  class-string  $class
     * @return array{id: string, label: string, sort: int, slug: string, root: string, class: class-string, navigation: bool}
     */
    private static function page(
        string $id,
        string $label,
        int $sort,
        string $slug,
        string $root,
        string $class,
        bool $navigation = true,
    ): array {
        return [
            'id' => $id,
            'label' => $label,
            'sort' => $sort,
            'slug' => $slug,
            'root' => $root,
            'class' => $class,
            'navigation' => $navigation,
        ];
    }
}
