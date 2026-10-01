<?php

declare(strict_types=1);

namespace Modules\Core\Support;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Modules\Core\Models\Setting;
use Throwable;

/**
 * Clés éditables depuis l'administration.
 * Les secrets restent masqués et ne remplacent la configuration que s'ils sont renseignés.
 */
final class SiteSettings
{
    /**
     * @return array<string, array{
     *     label: string,
     *     section: string,
     *     type: string,
     *     public: bool,
     *     secret: bool,
     *     default: string,
     *     span: string
     * }>
     */
    public static function fields(): array
    {
        return [
            'site.address' => self::field('Adresse', 'contact', default: '28M Cité des Magistrats, Sud Foire', public: true, span: 'full'),
            'site.city' => self::field('Ville', 'contact', default: 'Dakar', public: true),
            'site.phone' => self::field('Téléphone', 'contact', default: '+221 77 123 45 67', public: true),
            'site.email' => self::field('E-mail de contact', 'contact', default: 'contact@nujumalhuda.com', public: true),
            'site.latitude' => self::field('Latitude', 'contact', type: 'float', default: '14.7437965', public: true),
            'site.longitude' => self::field('Longitude', 'contact', type: 'float', default: '-17.4674915', public: true),
            'site.footer_note' => self::field('Note sous l’adresse', 'footer', type: 'text', public: true, span: 'full'),

            'footer.logo' => self::field('Logo', 'footer', type: 'image', public: true, span: 'full'),
            'footer.title_fr' => self::field('Titre (français)', 'footer', default: 'Nujum Al-Huda Institute Center', public: true),
            'footer.title_en' => self::field('Titre (anglais)', 'footer', default: 'Nujum Al-Huda Institute Center', public: true),
            'footer.title_ar' => self::field('Titre (arabe)', 'footer', default: 'مركز معهد نجوم الهدى', public: true),
            'footer.blurb_fr' => self::field('Description (français)', 'footer', type: 'text', default: 'Institut franco-anglo-arabe à Dakar. Le Coran, la langue arabe, les sciences islamiques et les œuvres de Cheikh Ibrahim Niasse.', public: true, span: 'full'),
            'footer.blurb_en' => self::field('Description (anglais)', 'footer', type: 'text', default: 'Franco-Anglo-Arabic institute in Dakar. The Quran, the Arabic language, the Islamic sciences and the works of Sheikh Ibrahim Niasse.', public: true, span: 'full'),
            'footer.blurb_ar' => self::field('Description (arabe)', 'footer', type: 'text', default: 'معهد فرنسي إنجليزي عربي في دكار. القرآن واللغة العربية والعلوم الإسلامية ومؤلفات الشيخ إبراهيم نياس.', public: true, span: 'full'),
            'footer.useful_title_fr' => self::field('Titre des liens utiles (français)', 'links', default: 'Liens utiles', public: true),
            'footer.useful_title_en' => self::field('Titre des liens utiles (anglais)', 'links', default: 'Useful links', public: true),
            'footer.useful_title_ar' => self::field('Titre des liens utiles (arabe)', 'links', default: 'روابط مفيدة', public: true),
            'footer.other_title_fr' => self::field('Titre des autres liens (français)', 'links', default: 'Autres liens', public: true),
            'footer.other_title_en' => self::field('Titre des autres liens (anglais)', 'links', default: 'Other links', public: true),
            'footer.other_title_ar' => self::field('Titre des autres liens (arabe)', 'links', default: 'روابط أخرى', public: true),
            'footer.useful_links' => self::field('Liens utiles', 'links', type: 'links', public: true, span: 'full'),
            'footer.other_links' => self::field('Autres liens', 'links', type: 'links', public: true, span: 'full'),
            'footer.newsletter_enabled' => self::field('Newsletter affichée', 'newsletter', type: 'boolean', default: '1', public: true),
            'footer.newsletter_placeholder_fr' => self::field('Champ e-mail (français)', 'newsletter', default: 'Votre e-mail', public: true),
            'footer.newsletter_placeholder_en' => self::field('Champ e-mail (anglais)', 'newsletter', default: 'Your email', public: true),
            'footer.newsletter_placeholder_ar' => self::field('Champ e-mail (arabe)', 'newsletter', default: 'بريدك', public: true),
            'footer.newsletter_button_fr' => self::field('Bouton (français)', 'newsletter', default: 'S\'abonner', public: true),
            'footer.newsletter_button_en' => self::field('Bouton (anglais)', 'newsletter', default: 'Subscribe', public: true),
            'footer.newsletter_button_ar' => self::field('Bouton (arabe)', 'newsletter', default: 'اشترك', public: true),

            'analytics.measurement_id' => self::field('Identifiant de mesure', 'analytics', public: true, span: 'full'),

            'mail.host' => self::field('Serveur SMTP', 'mail'),
            'mail.port' => self::field('Port', 'mail', type: 'integer', default: '587'),
            'mail.encryption' => self::field('Chiffrement', 'mail', default: 'tls'),
            'mail.username' => self::field('Utilisateur', 'mail'),
            'mail.password' => self::field('Mot de passe', 'mail', secret: true),
            'mail.from_address' => self::field('Adresse d’expédition', 'mail', default: 'noreply@nujumalhuda.com'),
            'mail.from_name' => self::field('Nom d’expédition', 'mail', default: 'Nujum Al-Huda Institute', span: 'full'),

            'sms.sender_name' => self::field('Nom d’expéditeur', 'sms', default: 'NujumAlHuda'),
            'sms.sender_address' => self::field('Adresse d’expéditeur', 'sms'),
            'sms.client_id' => self::field('Client ID', 'sms', secret: true),
            'sms.client_secret' => self::field('Client secret', 'sms', secret: true),

            'wave.key' => self::field('Clé API', 'wave', secret: true),
            'wave.secret' => self::field('Secret', 'wave', secret: true),
            'wave.webhook_secret' => self::field('Secret du webhook', 'wave', secret: true, span: 'full'),

            'orange_money.merchant_key' => self::field('Clé marchand', 'orange_money', secret: true, span: 'full'),
            'orange_money.client_id' => self::field('Client ID', 'orange_money', secret: true),
            'orange_money.client_secret' => self::field('Client secret', 'orange_money', secret: true),
        ];
    }

    /**
     * @return array<string, array{title: string, description: string}>
     */
    public static function sections(): array
    {
        return [
            'contact' => [
                'title' => 'Coordonnées',
                'description' => 'Adresse, téléphone et e-mail affichés sur le site. Le lieu se choisit sur la carte.',
            ],
            'footer' => [
                'title' => 'Pied de page',
                'description' => 'Logo, titre et description. Les textes sont préremplis en français, anglais et arabe.',
            ],
            'links' => [
                'title' => 'Liens',
                'description' => 'Colonnes « Liens utiles » et « Autres liens ». Les textes sont préremplis en français, anglais et arabe.',
            ],
            'analytics' => [
                'title' => 'Google Analytics',
                'description' => 'Identifiant de mesure, par exemple G-XXXXXXXX. Laisser vide pour ne pas charger Analytics.',
            ],
            'mail' => [
                'title' => 'E-mail',
                'description' => 'Envoi des messages de l’institut. Un champ secret vide conserve la clé déjà enregistrée.',
            ],
            'sms' => [
                'title' => 'SMS',
                'description' => 'Compte Orange SMS utilisé pour les notifications.',
            ],
            'wave' => [
                'title' => 'Paiement Wave',
                'description' => 'Clés de l’API Wave pour les dons et les paiements.',
            ],
            'orange_money' => [
                'title' => 'Paiement Orange Money',
                'description' => 'Clés marchand Orange Money.',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function formState(): array
    {
        $state = [];

        foreach (self::fields() as $key => $field) {
            if ($field['secret']) {
                $state[$key] = '';

                continue;
            }

            $state[$key] = match ($field['type']) {
                'boolean' => self::boolValue($key, $field['default'] === '1'),
                'links' => self::linksValue($key),
                'image' => self::raw($key) ?: null,
                default => self::value($key, $field['default']),
            };
        }

        // Filament lit « site.address » comme data.site.address, pas comme une clé plate.
        return Arr::undot($state);
    }

    public static function hasSecret(string $key): bool
    {
        $stored = self::raw($key);

        return $stored !== null && $stored !== '';
    }

    /**
     * @param  array<string, mixed>  $input
     */
    public static function persist(array $input): void
    {
        foreach (self::fields() as $key => $field) {
            if (! Arr::has($input, $key)) {
                continue;
            }

            $value = data_get($input, $key);

            if ($field['type'] === 'links') {
                data_set($input, $key, json_encode(self::sanitizeLinks(is_array($value) ? $value : []), JSON_UNESCAPED_UNICODE));
            } elseif ($field['type'] === 'image' && is_array($value)) {
                $first = reset($value);
                data_set($input, $key, is_string($first) ? $first : null);
            } elseif ($field['type'] === 'boolean') {
                data_set($input, $key, filter_var($value, FILTER_VALIDATE_BOOLEAN) ? '1' : '0');
            }
        }

        $input = Arr::dot($input);

        foreach (self::fields() as $key => $field) {
            if (! array_key_exists($key, $input)) {
                continue;
            }

            $value = $input[$key];

            if ($field['secret'] && ($value === null || $value === '')) {
                continue;
            }

            if ($field['type'] === 'image' && ($value === null || $value === '')) {
                $value = null;
            }

            Setting::query()->updateOrCreate(
                ['key' => $key],
                [
                    'value' => $value === null ? null : (string) $value,
                    'type' => match ($field['type']) {
                        'text', 'image' => 'string',
                        'links' => 'json',
                        default => $field['type'],
                    },
                    'group' => $field['section'],
                    'description' => $field['label'],
                    'is_public' => $field['public'],
                ],
            );
        }

        self::apply();
    }

    /**
     * @return array<string, mixed>
     */
    public static function publicPayload(): array
    {
        $latitude = (float) self::value('site.latitude', '14.7437965');
        $longitude = (float) self::value('site.longitude', '-17.4674915');

        return [
            'address' => (string) self::value('site.address', '28M Cité des Magistrats, Sud Foire'),
            'city' => (string) self::value('site.city', 'Dakar'),
            'phone' => (string) self::value('site.phone', '+221 77 123 45 67'),
            'email' => (string) self::value('site.email', 'contact@nujumalhuda.com'),
            'latitude' => $latitude,
            'longitude' => $longitude,
            'footer_note' => (string) self::value('site.footer_note', ''),
            'analytics_id' => (string) self::value('analytics.measurement_id', ''),
            'logo_url' => self::logoUrl(),
            'footer_title' => self::localeTrio('footer.title'),
            'footer_blurb' => self::localeTrio('footer.blurb'),
            'footer_useful_title' => self::localeTrio('footer.useful_title'),
            'footer_other_title' => self::localeTrio('footer.other_title'),
            'footer_useful_links' => self::linksPayload('footer.useful_links'),
            'footer_other_links' => self::linksPayload('footer.other_links'),
            'newsletter_enabled' => self::boolValue('footer.newsletter_enabled', true),
            'newsletter_placeholder' => self::localeTrio('footer.newsletter_placeholder'),
            'newsletter_button' => self::localeTrio('footer.newsletter_button'),
            'page_copy' => PlatformPages::publicCopy(),
            'centre_image_url' => PlatformPages::imageUrl('centre'),
            'founder_image_url' => PlatformPages::imageUrl('zawiya', 'founder_image'),
        ];
    }

    public static function apply(): void
    {
        if (! self::tableExists()) {
            return;
        }

        $map = [
            'mail.host' => 'mail.mailers.smtp.host',
            'mail.port' => 'mail.mailers.smtp.port',
            'mail.encryption' => 'mail.mailers.smtp.encryption',
            'mail.username' => 'mail.mailers.smtp.username',
            'mail.password' => 'mail.mailers.smtp.password',
            'mail.from_address' => 'mail.from.address',
            'mail.from_name' => 'mail.from.name',
            'sms.client_id' => 'services.orange_sms.client_id',
            'sms.client_secret' => 'services.orange_sms.client_secret',
            'sms.sender_name' => 'services.orange_sms.sender_name',
            'sms.sender_address' => 'services.orange_sms.sender_address',
            'wave.key' => 'services.wave.key',
            'wave.secret' => 'services.wave.secret',
            'wave.webhook_secret' => 'services.wave.webhook_secret',
            'orange_money.merchant_key' => 'services.orange_money.merchant_key',
            'orange_money.client_id' => 'services.orange_money.client_id',
            'orange_money.client_secret' => 'services.orange_money.client_secret',
        ];

        foreach ($map as $settingKey => $configKey) {
            $value = self::raw($settingKey);

            if ($value === null || $value === '') {
                continue;
            }

            $type = self::fields()[$settingKey]['type'] ?? 'string';
            Config::set($configKey, $type === 'integer' ? (int) $value : ($type === 'float' ? (float) $value : $value));
        }
    }

    public static function value(string $key, string $default = ''): string
    {
        $stored = self::raw($key);

        return ($stored === null || $stored === '') ? $default : $stored;
    }

    private static function raw(string $key): ?string
    {
        if (! self::tableExists()) {
            return null;
        }

        try {
            $value = Setting::query()->where('key', $key)->value('value');
        } catch (Throwable) {
            return null;
        }

        return $value === null ? null : (string) $value;
    }

    private static function boolValue(string $key, bool $default): bool
    {
        $stored = self::raw($key);

        if ($stored === null || $stored === '') {
            return $default;
        }

        return filter_var($stored, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * @return array{fr: string, en: string, ar: string}
     */
    private static function localeTrio(string $prefix): array
    {
        $fields = self::fields();
        $trio = [];

        foreach (['fr', 'en', 'ar'] as $locale) {
            $key = "{$prefix}_{$locale}";
            $trio[$locale] = (string) self::value($key, $fields[$key]['default'] ?? '');
        }

        return $trio;
    }

    private static function logoUrl(): ?string
    {
        $path = self::raw('footer.logo');

        if ($path === null || $path === '') {
            return null;
        }

        return Storage::disk('public')->url($path);
    }

    /**
     * @return array<int, array{href: string, label_fr: string, label_en: string, label_ar: string}>
     */
    private static function linksValue(string $key): array
    {
        $stored = self::raw($key);

        if ($stored === null || $stored === '') {
            return self::defaultLinks($key);
        }

        $decoded = json_decode($stored, true);

        return is_array($decoded) ? self::sanitizeLinks($decoded) : self::defaultLinks($key);
    }

    /**
     * @return array<int, array{href: string, label: array{fr: string, en: string, ar: string}}>
     */
    private static function linksPayload(string $key): array
    {
        return array_map(fn (array $row): array => [
            'href' => $row['href'],
            'label' => [
                'fr' => $row['label_fr'],
                'en' => $row['label_en'] !== '' ? $row['label_en'] : $row['label_fr'],
                'ar' => $row['label_ar'] !== '' ? $row['label_ar'] : $row['label_fr'],
            ],
        ], self::linksValue($key));
    }

    /**
     * @param  array<int, mixed>  $rows
     * @return array<int, array{href: string, label_fr: string, label_en: string, label_ar: string}>
     */
    private static function sanitizeLinks(array $rows): array
    {
        $clean = [];

        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }

            $href = trim((string) ($row['href'] ?? ''));

            if ($href === '') {
                continue;
            }

            if (! str_starts_with($href, '/') && ! preg_match('#^https?://#i', $href)) {
                $href = '/'.$href;
            }

            $clean[] = [
                'href' => $href,
                'label_fr' => trim((string) ($row['label_fr'] ?? '')),
                'label_en' => trim((string) ($row['label_en'] ?? '')),
                'label_ar' => trim((string) ($row['label_ar'] ?? '')),
            ];
        }

        return $clean;
    }

    /**
     * @return array<int, array{href: string, label_fr: string, label_en: string, label_ar: string}>
     */
    private static function defaultLinks(string $key): array
    {
        $useful = [
            self::link('/centre', 'Le centre', 'The center', 'المركز'),
            self::link('/zawiya', 'Zawiya', 'Zawiya', 'الزاوية'),
            self::link('/mosque/prayer-times', 'Horaires de prière', 'Prayer times', 'مواقيت الصلاة'),
            self::link('/centre/calendrier', 'Calendrier hégirien', 'Hijri calendar', 'التقويم الهجري'),
            self::link('/centre/khutbas', 'Khutbas', 'Khutbas', 'الخطب'),
            self::link('/centre/evenements', 'Événements', 'Events', 'الفعاليات'),
            self::link('/centre/annonces', 'Annonces', 'Announcements', 'الإعلانات'),
        ];

        $other = [
            self::link('/programs', 'Programmes', 'Programmes', 'البرامج'),
            self::link('/teachers', 'Enseignants', 'Teachers', 'المدرّسون'),
            self::link('/academique/inscription', 'Inscription', 'Enrolment', 'التسجيل'),
            self::link('/academique/promotions', 'Promotions', 'Cohorts', 'الدفعات'),
            self::link('/academique/certificats', 'Certificats', 'Certificates', 'الشهادات'),
            self::link('/academique/ijaza', 'Ijaza', 'Ijaza', 'الإجازة'),
        ];

        return $key === 'footer.other_links' ? $other : $useful;
    }

    /**
     * @return array{href: string, label_fr: string, label_en: string, label_ar: string}
     */
    private static function link(string $href, string $fr, string $en, string $ar): array
    {
        return [
            'href' => $href,
            'label_fr' => $fr,
            'label_en' => $en,
            'label_ar' => $ar,
        ];
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
     * @return array{label: string, section: string, type: string, public: bool, secret: bool, default: string, span: string}
     */
    private static function field(
        string $label,
        string $section,
        string $type = 'string',
        string $default = '',
        bool $public = false,
        bool $secret = false,
        string $span = '1',
    ): array {
        return [
            'label' => $label,
            'section' => $section,
            'type' => $type,
            'public' => $public,
            'secret' => $secret,
            'default' => $default,
            'span' => $span,
        ];
    }
}
