<?php

namespace Modules\Resources\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Resources\Models\AudioRecitation;
use Modules\Resources\Models\DailyContent;
use Modules\Resources\Models\LibraryResource;
use Modules\Resources\Models\MuudRamadanRate;
use Modules\Resources\Models\ZakatRate;

class ResourcesSeeder extends Seeder
{
    public function run(): void
    {
        $library = [
            [
                'slug' => 'jawahir-al-maani',
                'title_i18n' => [
                    'fr' => 'Jawahir al-Ma‘ani',
                    'en' => 'Jawahir al-Maani',
                    'ar' => 'جواهر المعاني',
                ],
                'description_i18n' => [
                    'fr' => 'Œuvre de référence de la voie tijane. Texte déposé par l’institut.',
                    'en' => 'Reference work of the Tijani path. Text deposited by the institute.',
                    'ar' => 'مرجع من الطريق التجانية. نص مودع من المعهد.',
                ],
                'author' => 'Alioune Tamakh Cissé',
                'tradition' => 'baye_niasse',
                'kind' => 'pdf',
                'language' => 'ar',
                'display_order' => 1,
                'download_count' => 128,
            ],
            [
                'slug' => 'rimah-hizb-rahim',
                'title_i18n' => [
                    'fr' => 'Rimah Hizb al-Rahim',
                    'en' => 'Rimah Hizb al-Rahim',
                    'ar' => 'رماح حزب الرحيم',
                ],
                'description_i18n' => [
                    'fr' => 'Textes et enseignements liés à la voie de Cheikh Ibrahim Niasse.',
                    'en' => 'Texts and teachings linked to Shaykh Ibrahim Niasse’s path.',
                    'ar' => 'نصوص وتعاليم مرتبطة بطريق الشيخ إبراهيم نياس.',
                ],
                'author' => 'Omar al-Futi',
                'tradition' => 'baye_niasse',
                'kind' => 'pdf',
                'language' => 'ar',
                'display_order' => 2,
                'download_count' => 96,
            ],
            [
                'slug' => 'kashif-ilbas',
                'title_i18n' => [
                    'fr' => 'Kashif al-Ilbas',
                    'en' => 'Kashif al-Ilbas',
                    'ar' => 'كاشف الإلباس',
                ],
                'description_i18n' => [
                    'fr' => 'Écrit de Cheikh Ibrahim Niasse sur la voie et la connaissance.',
                    'en' => 'A writing of Shaykh Ibrahim Niasse on the path and knowledge.',
                    'ar' => 'من مؤلفات الشيخ إبراهيم نياس في الطريق والمعرفة.',
                ],
                'author' => 'Cheikh Ibrahim Niasse',
                'tradition' => 'baye_niasse',
                'kind' => 'pdf',
                'language' => 'ar',
                'display_order' => 3,
                'download_count' => 210,
            ],
            [
                'slug' => 'muwatta',
                'title_i18n' => [
                    'fr' => 'Al-Muwatta',
                    'en' => 'Al-Muwatta',
                    'ar' => 'الموطأ',
                ],
                'description_i18n' => [
                    'fr' => 'Recueil de l’imam Malik, référence de l’école malikite.',
                    'en' => 'Collection of Imam Malik, reference of the Maliki school.',
                    'ar' => 'موطأ الإمام مالك، مرجع المدرسة المالكية.',
                ],
                'author' => 'Malik ibn Anas',
                'tradition' => 'sunnite',
                'kind' => 'pdf',
                'language' => 'ar',
                'display_order' => 4,
                'download_count' => 175,
            ],
            [
                'slug' => 'risala-ibn-abi-zayd',
                'title_i18n' => [
                    'fr' => 'La Risala d’Ibn Abi Zayd',
                    'en' => 'The Risala of Ibn Abi Zayd',
                    'ar' => 'رسالة ابن أبي زيد',
                ],
                'description_i18n' => [
                    'fr' => 'Manuel classique de fiqh malikite pour les débutants et les enseignants.',
                    'en' => 'Classic Maliki fiqh manual for beginners and teachers.',
                    'ar' => 'متن فقهي مالكي كلاسيكي للمبتدئين والمدرّسين.',
                ],
                'author' => 'Ibn Abi Zayd al-Qayrawani',
                'tradition' => 'sunnite',
                'kind' => 'pdf',
                'language' => 'ar',
                'display_order' => 5,
                'download_count' => 142,
            ],
            [
                'slug' => 'arbain-nawawi',
                'title_i18n' => [
                    'fr' => 'Les Quarante hadiths de Nawawi',
                    'en' => 'Nawawi’s Forty Hadiths',
                    'ar' => 'الأربعون النووية',
                ],
                'description_i18n' => [
                    'fr' => 'Quarante hadiths fondamentaux pour le parcours de l’élève.',
                    'en' => 'Forty foundational hadiths for the student’s path.',
                    'ar' => 'أربعون حديثاً أساسية لمسار الطالب.',
                ],
                'author' => 'An-Nawawi',
                'tradition' => 'sunnite',
                'kind' => 'pdf',
                'language' => 'ar',
                'display_order' => 6,
                'download_count' => 188,
            ],
            [
                'slug' => 'cours-tajwid-bases',
                'title_i18n' => [
                    'fr' => 'Cours audio — Bases du tajwid',
                    'en' => 'Audio course — Tajweed basics',
                    'ar' => 'دورة صوتية — أساسيات التجويد',
                ],
                'description_i18n' => [
                    'fr' => 'Introduction orale aux règles essentielles du tajwid, pour révision.',
                    'en' => 'Oral introduction to essential tajweed rules, for revision.',
                    'ar' => 'مقدمة شفوية لقواعد التجويد الأساسية للمراجعة.',
                ],
                'author' => 'Nujum Al-Huda',
                'tradition' => 'sunnite',
                'kind' => 'audio',
                'language' => 'fr',
                'display_order' => 7,
                'download_count' => 64,
            ],
            [
                'slug' => 'discours-baye-niasse-intro',
                'title_i18n' => [
                    'fr' => 'Audio — Introduction à Baye Niasse',
                    'en' => 'Audio — Introduction to Baye Niasse',
                    'ar' => 'صوتي — مقدمة عن الشيخ إبراهيم نياس',
                ],
                'description_i18n' => [
                    'fr' => 'Présentation orale de la vie et de l’enseignement de Cheikh Ibrahim Niasse.',
                    'en' => 'Oral presentation of the life and teaching of Shaykh Ibrahim Niasse.',
                    'ar' => 'عرض شفوي لحياة وتعاليم الشيخ إبراهيم نياس.',
                ],
                'author' => 'Nujum Al-Huda',
                'tradition' => 'baye_niasse',
                'kind' => 'audio',
                'language' => 'fr',
                'display_order' => 8,
                'download_count' => 81,
            ],
        ];

        foreach ($library as $item) {
            LibraryResource::updateOrCreate(
                ['slug' => $item['slug']],
                array_merge($item, ['is_public' => true]),
            );
        }

        $recitations = [
            [
                'surah_number' => 1,
                'surah_name_i18n' => ['fr' => 'Al-Fâtiha', 'en' => 'Al-Fatiha', 'ar' => 'الفاتحة'],
                'reciter' => 'Mishary Rashid Alafasy',
                'audio_url' => 'https://cdn.islamic.network/quran/audio/128/ar.alafasy/1.mp3',
                'duration_seconds' => 48,
            ],
            [
                'surah_number' => 36,
                'surah_name_i18n' => ['fr' => 'Yâ-Sîn', 'en' => 'Ya-Sin', 'ar' => 'يس'],
                'reciter' => 'Mishary Rashid Alafasy',
                'audio_url' => 'https://cdn.islamic.network/quran/audio/128/ar.alafasy/36.mp3',
                'duration_seconds' => 1360,
            ],
            [
                'surah_number' => 67,
                'surah_name_i18n' => ['fr' => 'Al-Mulk', 'en' => 'Al-Mulk', 'ar' => 'الملك'],
                'reciter' => 'Mishary Rashid Alafasy',
                'audio_url' => 'https://cdn.islamic.network/quran/audio/128/ar.alafasy/67.mp3',
                'duration_seconds' => 450,
            ],
            [
                'surah_number' => 112,
                'surah_name_i18n' => ['fr' => 'Al-Ikhlâs', 'en' => 'Al-Ikhlas', 'ar' => 'الإخلاص'],
                'reciter' => 'Institut Nujum Al-Huda',
                'audio_url' => 'https://cdn.islamic.network/quran/audio/128/ar.alafasy/112.mp3',
                'duration_seconds' => 22,
            ],
            [
                'surah_number' => 113,
                'surah_name_i18n' => ['fr' => 'Al-Falaq', 'en' => 'Al-Falaq', 'ar' => 'الفلق'],
                'reciter' => 'Institut Nujum Al-Huda',
                'audio_url' => 'https://cdn.islamic.network/quran/audio/128/ar.alafasy/113.mp3',
                'duration_seconds' => 28,
            ],
            [
                'surah_number' => 114,
                'surah_name_i18n' => ['fr' => 'An-Nâs', 'en' => 'An-Nas', 'ar' => 'الناس'],
                'reciter' => 'Institut Nujum Al-Huda',
                'audio_url' => 'https://cdn.islamic.network/quran/audio/128/ar.alafasy/114.mp3',
                'duration_seconds' => 30,
            ],
            [
                'surah_number' => 18,
                'surah_name_i18n' => ['fr' => 'Al-Kahf', 'en' => 'Al-Kahf', 'ar' => 'الكهف'],
                'reciter' => 'Abdul Basit',
                'audio_url' => 'https://cdn.islamic.network/quran/audio/128/ar.abdulsamad/18.mp3',
                'duration_seconds' => 2100,
            ],
            [
                'surah_number' => 55,
                'surah_name_i18n' => ['fr' => 'Ar-Rahmân', 'en' => 'Ar-Rahman', 'ar' => 'الرحمن'],
                'reciter' => 'Abdul Basit',
                'audio_url' => 'https://cdn.islamic.network/quran/audio/128/ar.abdulsamad/55.mp3',
                'duration_seconds' => 780,
            ],
        ];

        foreach ($recitations as $item) {
            AudioRecitation::updateOrCreate(
                [
                    'surah_number' => $item['surah_number'],
                    'reciter' => $item['reciter'],
                ],
                [
                    'surah_name_i18n' => $item['surah_name_i18n'],
                    'audio_url' => $item['audio_url'],
                    'duration_seconds' => $item['duration_seconds'],
                    'is_public' => true,
                    'play_count' => random_int(10, 200),
                ],
            );
        }

        DailyContent::updateOrCreate(
            ['display_date' => now()->toDateString(), 'type' => 'verse'],
            [
                'arabic_text' => 'بِسْمِ اللَّهِ الرَّحْمَٰنِ الرَّحِيمِ',
                'translation_i18n' => [
                    'fr' => 'Au nom de Dieu, le Tout Miséricordieux, le Très Miséricordieux.',
                    'en' => 'In the name of God, the Entirely Merciful, the Especially Merciful.',
                    'ar' => 'بِسْمِ اللَّهِ الرَّحْمَٰنِ الرَّحِيمِ',
                ],
                'source' => 'Coran',
                'reference' => '1:1',
                'is_published' => true,
            ]
        );

        DailyContent::updateOrCreate(
            ['display_date' => now()->toDateString(), 'type' => 'hadith'],
            [
                'arabic_text' => 'إِنَّمَا الأَعْمَالُ بِالنِّيَّاتِ',
                'translation_i18n' => [
                    'fr' => 'Les actions ne valent que par les intentions.',
                    'en' => 'Actions are but by intentions.',
                    'ar' => 'إِنَّمَا الأَعْمَالُ بِالنِّيَّاتِ',
                ],
                'source' => 'Sahih al-Bukhari',
                'reference' => 'n° 1',
                'is_published' => true,
            ]
        );

        ZakatRate::updateOrCreate(
            ['prices_as_of' => now()->toDateString(), 'madhhab' => 'maliki'],
            [
                'nisab_basis' => 'silver',
                'gold_nisab_grams' => 85,
                'silver_nisab_grams' => 595,
                'gold_price_per_gram_minor' => 55000,
                'silver_price_per_gram_minor' => 800,
                'currency' => 'XOF',
                'rate_numerator' => 1,
                'rate_denominator' => 40,
                'source_i18n' => config('zakat.source'),
                'prices_are_indicative' => true,
                'is_current' => true,
            ]
        );
        MuudRamadanRate::updateOrCreate(
            ['hijri_year' => 1447, 'madhhab' => 'maliki'],
            [
                'amount_per_person_minor' => 2000,
                'currency' => 'XOF',
                'staple' => 'rice',
                'sa_grams' => 2400,
                'label_i18n' => [
                    'fr' => 'Muud Ramadan 1447 — zakat al-fitr',
                    'en' => 'Ramadan mudd 1447 — zakat al-fitr',
                    'ar' => 'مد رمضان 1447 — زكاة الفطر',
                ],
                'source_i18n' => [
                    'fr' => 'École malikite. La zakat al-fitr est d’un saʿ de nourriture de base (environ quatre mudd) par personne à charge, avant la prière de l’Aïd. Le montant en argent est l’équivalent annoncé par l’institut pour faciliter le don. Référence : al-Mudawwana.',
                    'en' => 'Maliki school. Zakat al-fitr is one saʿ of staple food (about four mudd) per dependent, before the Eid prayer. The cash amount is the institute’s announced equivalent to ease giving. Reference: al-Mudawwana.',
                    'ar' => 'المذهب المالكي. زكاة الفطر صاع من قوت البلد (نحو أربعة أمداد) عن كل نفس قبل صلاة العيد. المبلغ النقدي معادل يعلنه المعهد تيسيراً. المرجع: المدونة.',
                ],
                'prices_as_of' => now()->toDateString(),
                'prices_are_indicative' => true,
                'is_current' => true,
            ]
        );


        $this->command?->info('✅ Ressources & récitations synchronisées.');
    }
}
