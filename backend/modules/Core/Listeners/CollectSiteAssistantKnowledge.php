<?php

declare(strict_types=1);

namespace Modules\Core\Listeners;

use Modules\Core\Events\AssistantKnowledgeCollecting;
use Modules\Core\Support\EditorialText;
use Modules\Core\Support\SiteSettings;
use Throwable;

class CollectSiteAssistantKnowledge
{
    public function handle(AssistantKnowledgeCollecting $event): void
    {
        try {
            $this->collect($event);
        } catch (Throwable) {
            // Les coordonnées par défaut restent utilisables via SiteSettings::value.
        }
    }

    private function collect(AssistantKnowledgeCollecting $event): void
    {
        $payload = SiteSettings::publicPayload();
        $locale = $event->locale;

        $address = trim((string) ($payload['address'] ?? ''));
        $city = trim((string) ($payload['city'] ?? ''));
        $phone = trim((string) ($payload['phone'] ?? ''));
        $email = trim((string) ($payload['email'] ?? ''));
        $place = trim($address.($city !== '' ? ', '.$city : ''));

        $event->add(
            'contact',
            $this->label($locale, 'Coordonnées', 'Contact details', 'بيانات الاتصال'),
            implode("\n", array_filter([
                $place !== '' ? $this->label($locale, 'Adresse : '.$place, 'Address: '.$place, 'العنوان: '.$place) : null,
                $phone !== '' ? $this->label($locale, 'Téléphone : '.$phone, 'Phone: '.$phone, 'الهاتف: '.$phone) : null,
                $email !== '' ? $this->label($locale, 'E-mail : '.$email, 'Email: '.$email, 'البريد: '.$email) : null,
            ])),
            '/'.$locale.'/centre/contact',
        );

        $blurb = EditorialText::present(
            is_array($payload['footer_blurb'] ?? null) ? $payload['footer_blurb'] : null,
            $locale,
        )['text'];
        $title = EditorialText::present(
            is_array($payload['footer_title'] ?? null) ? $payload['footer_title'] : null,
            $locale,
        )['text'];

        if ($title !== '' || $blurb !== '') {
            $event->add(
                'institute',
                $title !== '' ? $title : 'Nujum Al-Huda Institute Center',
                $blurb,
                '/'.$locale,
            );
        }

        $wave = trim((string) data_get($payload, 'payment_contacts.wave', ''));
        $orange = trim((string) data_get($payload, 'payment_contacts.orange_money', ''));
        if ($wave !== '' || $orange !== '') {
            $event->add(
                'payments',
                $this->label($locale, 'Paiements', 'Payments', 'المدفوعات'),
                implode("\n", array_filter([
                    $wave !== '' ? 'Wave : '.$wave : null,
                    $orange !== '' ? 'Orange Money : '.$orange : null,
                ])),
                '/'.$locale.'/centre/contact',
            );
        }

        $event->add(
            'enroll.index',
            $this->label($locale, 'Inscription', 'Enrollment', 'التسجيل'),
            match ($locale) {
                'en' => "To enroll at Nujum Al-Huda:\n1. Open the enrolment page.\n2. Choose an open programme and promotion.\n3. Fill in the form (student details, motivation, emergency contact).\n4. Attach the required documents if asked (ID, birth certificate, photo).\n5. Send the request and keep the tracking code to follow the file.\nThe secretariat then reviews the application.",
                'ar' => "للتسجيل في نجوم الهدى:\n1. افتح صفحة التسجيل.\n2. اختر برنامجاً وترقية مفتوحين.\n3. املأ الاستمارة (بيانات التلميذ، الدافع، جهة اتصال للطوارئ).\n4. أرفق الوثائق المطلوبة إن وُجدت.\n5. أرسل الطلب واحتفظ برمز المتابعة.\nثم يراجع السكرتارية الطلب.",
                default => "Pour s’inscrire à Nujum Al-Huda :\n1. Ouvrez la page Inscription.\n2. Choisissez un programme et une promotion ouverte.\n3. Remplissez le formulaire (élève, motivation, contact d’urgence).\n4. Ajoutez les pièces demandées si besoin (identité, acte de naissance, photo).\n5. Envoyez la demande et gardez le code de suivi pour suivre le dossier.\nLe secrétariat valide ensuite la place.",
            },
            '/'.$locale.'/academique/inscription',
        );

        $this->pageCopy($event, is_array($payload['page_copy'] ?? null) ? $payload['page_copy'] : []);
    }

    /**
     * @param  array<string, mixed>  $tree
     */
    private function pageCopy(AssistantKnowledgeCollecting $event, array $tree): void
    {
        $paths = [
            'doors.centre' => '/centre',
            'pages.zawiya' => '/centre/zawiya',
            'pages.prayers' => '/centre/horaires',
            'pages.programs' => '/academique/programmes',
            'pages.teachers' => '/academique/enseignants',
            'pages.enroll' => '/academique/inscription',
            'pages.contact' => '/centre/contact',
            'pages.live' => '/direct',
            'pages.dahira' => '/centre/dahiras',
            'pages.zakat' => '/centre/zakat',
            'news' => '/actualites',
        ];

        foreach ($paths as $root => $url) {
            $node = data_get($tree, $root);
            if (! is_array($node)) {
                continue;
            }
            $parts = $this->flattenLocalized($node, $event->locale);
            if ($parts === []) {
                continue;
            }
            $event->add(
                'page.'.str_replace('.', '-', $root),
                $parts[0],
                implode("\n", array_slice($parts, 1)),
                '/'.$event->locale.$url,
            );
        }
    }

    /**
     * @param  array<string, mixed>  $node
     * @return list<string>
     */
    private function flattenLocalized(array $node, string $locale): array
    {
        $out = [];
        $walk = function (mixed $value) use (&$walk, &$out, $locale): void {
            if (! is_array($value)) {
                return;
            }
            if (array_key_exists('fr', $value) || array_key_exists('en', $value) || array_key_exists('ar', $value)) {
                $text = trim(EditorialText::present($value, $locale)['text']);
                if ($text !== '') {
                    $out[] = $text;
                }

                return;
            }
            foreach ($value as $child) {
                $walk($child);
            }
        };
        $walk($node);

        return array_values(array_unique($out));
    }

    private function label(string $locale, string $fr, string $en, string $ar): string
    {
        return match ($locale) {
            'en' => $en,
            'ar' => $ar,
            default => $fr,
        };
    }
}
