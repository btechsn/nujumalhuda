<?php

namespace Modules\News\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Modules\Core\Models\Media;
use Modules\Core\Models\Organization;
use Modules\Core\Models\User;
use Modules\News\Models\Article;
use Modules\News\Models\ArticleCategory;

class ArticleSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(CategorySeeder::class);

        $organizationId = Organization::query()->where('slug', 'nujum-al-huda')->value('id');
        $author = User::query()->where('email', 'admin@nujumalhuda.com')->first() ?? User::query()->first();
        $categories = ArticleCategory::query()->get()->keyBy('slug');

        if (! $author || $categories->isEmpty() || ! $organizationId) {
            $this->command?->warn('⚠️  Organisation, catégories ou auteur manquants.');

            return;
        }

        $articles = [
            [
                'category' => 'vie-du-centre',
                'cover' => 'slide-centre.jpg',
                'title_i18n' => [
                    'fr' => 'Rentrée 2026-2027 : inscriptions ouvertes',
                    'en' => '2026-2027 intake: enrollments open',
                    'ar' => 'الدخول المدرسي 2026-2027: التسجيل مفتوح',
                ],
                'excerpt_i18n' => [
                    'fr' => 'L’institut ouvre les inscriptions pour la nouvelle année académique. Programmes Coran, arabe et sciences islamiques.',
                    'en' => 'The institute opens enrollments for the new academic year. Qur’an, Arabic and Islamic sciences programmes.',
                    'ar' => 'يفتح المعهد باب التسجيل للعام الدراسي الجديد: قرآن وعربية وعلوم إسلامية.',
                ],
                'content_i18n' => [
                    'fr' => '<p>L’institut Nujum Al-Huda annonce l’ouverture des inscriptions pour l’année 2026-2027.</p><p>Les parcours proposés couvrent la mémorisation du Coran, la langue arabe, les sciences islamiques et l’étude des œuvres de Cheikh Ibrahim Niasse.</p><p>Les dossiers se déposent au centre (28M Cité des Magistrats, Sud Foire, Dakar) ou via le formulaire d’inscription en ligne.</p>',
                    'en' => '<p>Nujum Al-Huda Institute announces enrollments for 2026-2027.</p><p>Paths include Qur’an memorization, Arabic, Islamic sciences and the study of Shaykh Ibrahim Niasse’s works.</p><p>Applications can be submitted at the centre or via the online form.</p>',
                    'ar' => '<p>يعلن معهد نجوم الهدى عن فتح التسجيل للعام 2026-2027.</p><p>تشمل المسارات حفظ القرآن والعربية والعلوم الإسلامية ودراسة أعمال الشيخ إبراهيم نياس.</p>',
                ],
                'slug' => 'rentree-2026-2027-inscriptions',
                'tags' => ['inscription', 'rentrée', 'programmes'],
                'is_featured' => true,
                'days_ago' => 1,
            ],
            [
                'category' => 'evenements',
                'cover' => 'slide-actualites.jpg',
                'title_i18n' => [
                    'fr' => 'Conférence : les valeurs de l’Islam — 5 octobre',
                    'en' => 'Conference: Islamic values — 5 October',
                    'ar' => 'محاضرة: قيم الإسلام — 5 أكتوبر',
                ],
                'excerpt_i18n' => [
                    'fr' => 'Conférence ouverte au public à 16h, avec Cheikh Abdoulaye Diop. Entrée libre.',
                    'en' => 'Public conference at 4 pm with Cheikh Abdoulaye Diop. Free entry.',
                    'ar' => 'محاضرة عامة الساعة 16 مع الشيخ عبد الله ديوب. الدخول حر.',
                ],
                'content_i18n' => [
                    'fr' => '<p>L’institut organise une conférence ouverte le <strong>5 octobre 2026 à 16h</strong>.</p><p>Thème : justice, fraternité, compassion et paix dans l’Islam.</p><p>Intervenant : Cheikh Abdoulaye Diop. Lieu : salle principale du centre. Entrée libre.</p>',
                    'en' => '<p>The institute hosts a public conference on <strong>5 October 2026 at 4 pm</strong>.</p><p>Theme: justice, fraternity, compassion and peace in Islam.</p><p>Speaker: Cheikh Abdoulaye Diop. Free entry.</p>',
                    'ar' => '<p>ينظّم المعهد محاضرة عامة يوم <strong>5 أكتوبر 2026 الساعة 16</strong>.</p><p>الموضوع: العدل والأخوة والرحمة والسلام في الإسلام.</p>',
                ],
                'slug' => 'conference-valeurs-islam-5-octobre',
                'tags' => ['conférence', 'événement', 'valeurs'],
                'is_featured' => true,
                'days_ago' => 2,
            ],
            [
                'category' => 'enseignements',
                'cover' => 'slide-recitation.jpg',
                'title_i18n' => [
                    'fr' => 'L’importance du tajwid dans la récitation',
                    'en' => 'Why tajweed matters in recitation',
                    'ar' => 'أهمية التجويد في التلاوة',
                ],
                'excerpt_i18n' => [
                    'fr' => 'Le tajwid guide la prononciation et le respect du texte révélé. Un pilier de nos cours.',
                    'en' => 'Tajweed guides pronunciation and respect for the revealed text. A pillar of our classes.',
                    'ar' => 'التجويد يضبط النطق ويحفظ حق النص. ركن من دروسنا.',
                ],
                'content_i18n' => [
                    'fr' => '<p>Le tajwid enseigne la bonne articulation des lettres et les règles de lecture du Coran.</p><p>À Nujum Al-Huda, chaque élève progresse avec un suivi personnalisé, du niveau débutant au perfectionnement.</p><p>Des quizz d’entraînement sont aussi disponibles en ligne pour réviser.</p>',
                    'en' => '<p>Tajweed teaches letter articulation and Qur’anic reading rules.</p><p>At Nujum Al-Huda, each student advances with personal follow-up.</p><p>Practice quizzes are also available online.</p>',
                    'ar' => '<p>يعلّم التجويد مخارج الحروف وأحكام التلاوة.</p><p>في نجوم الهدى يتابع كل طالب تقدّمه برفقة المدرّس.</p>',
                ],
                'slug' => 'importance-tajwid-recitation',
                'tags' => ['tajwid', 'coran', 'récitation'],
                'is_featured' => false,
                'days_ago' => 4,
            ],
            [
                'category' => 'communaute',
                'cover' => 'intro-lecon.jpg',
                'title_i18n' => [
                    'fr' => 'Témoignage : mon parcours à Nujum Al-Huda',
                    'en' => 'Testimony: my path at Nujum Al-Huda',
                    'ar' => 'شهادة: مساري في نجوم الهدى',
                ],
                'excerpt_i18n' => [
                    'fr' => 'Moussa, ancien élève, raconte trois années de mémorisation et de fraternité au centre.',
                    'en' => 'Moussa, a former student, shares three years of memorization and fellowship at the centre.',
                    'ar' => 'موسى، طالب سابق، يروي ثلاث سنوات من الحفظ والأخوة في المركز.',
                ],
                'content_i18n' => [
                    'fr' => '<p>« J’ai étudié trois ans à Nujum Al-Huda. Grâce aux enseignants et à l’ambiance du centre, j’ai mémorisé dix juz et approfondi ma compréhension. »</p><p>Moussa recommande l’institut à quiconque cherche un cadre sérieux et bienveillant.</p>',
                    'en' => '<p>“I studied three years at Nujum Al-Huda. Thanks to the teachers and the centre’s atmosphere, I memorized ten juz and deepened my understanding.”</p>',
                    'ar' => '<p>«درست ثلاث سنوات في نجوم الهدى، وحفظت عشرة أجزاء بفضل المدرّسين وأجواء المركز.»</p>',
                ],
                'slug' => 'temoignage-parcours-nujum-al-huda',
                'tags' => ['témoignage', 'élève', 'communauté'],
                'is_featured' => true,
                'days_ago' => 6,
            ],
            [
                'category' => 'vie-du-centre',
                'cover' => 'slide-priere.jpg',
                'title_i18n' => [
                    'fr' => 'Horaires de prière mis à jour pour le mois',
                    'en' => 'Updated prayer times for the month',
                    'ar' => 'تحديث مواقيت الصلاة لهذا الشهر',
                ],
                'excerpt_i18n' => [
                    'fr' => 'Consultez les horaires et l’iqama de la mosquée du centre pour ce mois.',
                    'en' => 'Check the mosque prayer and iqama times for this month.',
                    'ar' => 'اطّلع على مواقيت الصلاة والإقامة في مسجد المركز لهذا الشهر.',
                ],
                'content_i18n' => [
                    'fr' => '<p>Les horaires de prière du centre sont actualisés chaque mois.</p><p>Vous les trouverez sur la page Centre, avec l’iqama annoncée pour la mosquée.</p><p>Adresse : 28M Cité des Magistrats, Sud Foire, Dakar.</p>',
                    'en' => '<p>Centre prayer times are updated monthly.</p><p>Find them on the Centre page, with the announced iqama.</p>',
                    'ar' => '<p>تُحدَّث مواقيت الصلاة شهرياً.</p><p>تجدها في صفحة المركز مع الإقامة المعلنة.</p>',
                ],
                'slug' => 'horaires-priere-mois',
                'tags' => ['prière', 'mosquée', 'horaires'],
                'is_featured' => false,
                'days_ago' => 3,
            ],
            [
                'category' => 'enseignements',
                'cover' => 'slide-academique.jpg',
                'title_i18n' => [
                    'fr' => 'Nouveau parcours arabe : niveau débutant',
                    'en' => 'New Arabic path: beginner level',
                    'ar' => 'مسار عربي جديد: مستوى المبتدئين',
                ],
                'excerpt_i18n' => [
                    'fr' => 'Un parcours progressif pour lire, comprendre et progresser en arabe classique.',
                    'en' => 'A progressive path to read, understand and grow in classical Arabic.',
                    'ar' => 'مسار تدريجي للقراءة والفهم والتقدّم في العربية الفصحى.',
                ],
                'content_i18n' => [
                    'fr' => '<p>Le parcours arabe débutant s’adresse à ceux qui démarrent ou reprennent les bases.</p><p>Alphabet, lecture, vocabulaire et premières phrases — avec un suivi en promotion.</p><p>Les inscriptions se font via la page Académique.</p>',
                    'en' => '<p>The beginner Arabic path is for those starting or rebuilding the foundations.</p><p>Alphabet, reading, vocabulary and first sentences — with cohort follow-up.</p>',
                    'ar' => '<p>مسار العربية للمبتدئين لمن يبدأ أو يعيد البناء.</p><p>الحروف والقراءة والمفردات والجمل الأولى.</p>',
                ],
                'slug' => 'parcours-arabe-debutant',
                'tags' => ['arabe', 'débutant', 'programme'],
                'is_featured' => false,
                'days_ago' => 8,
            ],
            [
                'category' => 'evenements',
                'cover' => 'slide-zawiya-soir.png',
                'title_i18n' => [
                    'fr' => 'Soirée de récitation à la zawiya',
                    'en' => 'Recitation evening at the zawiya',
                    'ar' => 'أمسية تلاوة في الزاوية',
                ],
                'excerpt_i18n' => [
                    'fr' => 'Une soirée ouverte pour écouter et accompagner les élèves en récitation.',
                    'en' => 'An open evening to listen and support students in recitation.',
                    'ar' => 'أمسية مفتوحة للاستماع ومرافقة الطلاب في التلاوة.',
                ],
                'content_i18n' => [
                    'fr' => '<p>La zawiya accueille une soirée de récitation ouverte à la communauté.</p><p>Les élèves présentent des passages travaillés en classe ; les enseignants accompagnent.</p><p>Venez nombreux partager ce moment de recueillement.</p>',
                    'en' => '<p>The zawiya hosts an open recitation evening for the community.</p><p>Students present passages prepared in class; teachers guide.</p>',
                    'ar' => '<p>تستضيف الزاوية أمسية تلاوة مفتوحة للمجتمع.</p><p>يعرض الطلاب مقاطع درسوها ويرافقهم المدرّسون.</p>',
                ],
                'slug' => 'soiree-recitation-zawiya',
                'tags' => ['zawiya', 'récitation', 'soirée'],
                'is_featured' => false,
                'days_ago' => 10,
            ],
            [
                'category' => 'communaute',
                'cover' => 'slide-live.jpg',
                'title_i18n' => [
                    'fr' => 'Cours en direct : rejoignez le studio',
                    'en' => 'Live classes: join the studio',
                    'ar' => 'دروس مباشرة: انضموا إلى الاستوديو',
                ],
                'excerpt_i18n' => [
                    'fr' => 'Certains cours sont diffusés en direct. Retrouvez le calendrier sur la page Direct.',
                    'en' => 'Some classes are streamed live. See the schedule on the Live page.',
                    'ar' => 'تُبث بعض الدروس مباشرة. راجعوا الجدول في صفحة البث.',
                ],
                'content_i18n' => [
                    'fr' => '<p>Le studio de Nujum Al-Huda diffuse régulièrement des cours et des moments de prière.</p><p>Pour suivre le prochain live, rendez-vous dans la rubrique Direct du site.</p><p>Les replays sont disponibles ensuite pour réviser.</p>',
                    'en' => '<p>The Nujum Al-Huda studio regularly streams classes and prayer moments.</p><p>Check the Live section for the next broadcast. Replays follow for review.</p>',
                    'ar' => '<p>يبث استوديو نجوم الهدى دروساً ولحظات صلاة بانتظام.</p><p>تابعوا قسم البث للجلسة القادمة.</p>',
                ],
                'slug' => 'cours-en-direct-studio',
                'tags' => ['live', 'studio', 'cours'],
                'is_featured' => false,
                'days_ago' => 12,
            ],
            [
                'category' => 'vie-du-centre',
                'cover' => 'slide-replay.jpg',
                'title_i18n' => [
                    'fr' => 'Remise d’attestations de niveau',
                    'en' => 'Level certificates ceremony',
                    'ar' => 'حفل تسليم إفادات المستوى',
                ],
                'excerpt_i18n' => [
                    'fr' => 'Des élèves ont reçu leur attestation de suivi. Ces documents ne remplacent pas l’ijaza.',
                    'en' => 'Students received their study attestations. These documents are not ijazas.',
                    'ar' => 'تسلّم طلاب إفادات المتابعة. هذه الوثائق ليست إجازات.',
                ],
                'content_i18n' => [
                    'fr' => '<p>Le centre a remis des attestations de niveau aux élèves ayant validé un palier de hifz.</p><p>Chaque attestation porte un code de vérification. Elle constate un parcours suivi ; elle n’est pas une ijaza.</p><p>La liste des certifiés est consultable sur la page Académique → Certificats.</p>',
                    'en' => '<p>The centre issued level attestations to students who completed a hifz milestone.</p><p>Each attestation has a verification code. It is not an ijaza.</p>',
                    'ar' => '<p>سلّم المركز إفادات مستوى لمن أتمّ مرحلة من الحفظ.</p><p>لكل إفادة رمز تحقق، وليست إجازة.</p>',
                ],
                'slug' => 'remise-attestations-niveau',
                'tags' => ['attestations', 'certificats', 'hifz'],
                'is_featured' => true,
                'days_ago' => 5,
            ],
            [
                'category' => 'enseignements',
                'cover' => 'slide-quizz.jpg',
                'title_i18n' => [
                    'fr' => 'Quizz d’entraînement : révisez en ligne',
                    'en' => 'Practice quizzes: revise online',
                    'ar' => 'اختبارات تدريب: راجعوا عبر الإنترنت',
                ],
                'excerpt_i18n' => [
                    'fr' => 'Tajwid, Coran, arabe, fiqh… des quizz libres pour mesurer ce qui a été retenu.',
                    'en' => 'Tajweed, Qur’an, Arabic, fiqh… free quizzes to check what you retained.',
                    'ar' => 'تجويد وقرآن وعربية وفقه… اختبارات حرّة لقياس ما حفظتم.',
                ],
                'content_i18n' => [
                    'fr' => '<p>Les quizz publics de Nujum Al-Huda sont en accès libre : pas de compte requis.</p><p>Répondez, validez, et voyez tout de suite votre score (réussite à partir de 60&nbsp;%).</p><p>Rendez-vous dans Académique → Quizz.</p>',
                    'en' => '<p>Public quizzes at Nujum Al-Huda are free to take — no account required.</p><p>Answer, submit, and see your score immediately (pass from 60%).</p>',
                    'ar' => '<p>اختبارات نجوم الهدى العامة متاحة دون حساب.</p><p>أجبوا ثم شاهدوا نتيجتكم فوراً (النجاح من 60٪).</p>',
                ],
                'slug' => 'quizz-entrainement-en-ligne',
                'tags' => ['quizz', 'révision', 'académique'],
                'is_featured' => false,
                'days_ago' => 9,
            ],
        ];

        $count = 0;
        $slugs = [];

        foreach ($articles as $data) {
            $category = $categories->get($data['category']);
            $coverId = $this->ensureCoverMedia($data['cover'], $author->id, $organizationId);
            $slugs[] = $data['slug'];

            Article::query()->updateOrCreate(
                ['slug' => $data['slug']],
                [
                    'organization_id' => $organizationId,
                    'author_id' => $author->id,
                    'category_id' => $category?->id,
                    'title_i18n' => $data['title_i18n'],
                    'excerpt_i18n' => $data['excerpt_i18n'],
                    'content_i18n' => $data['content_i18n'],
                    'tags' => $data['tags'],
                    'is_featured' => $data['is_featured'],
                    'status' => 'published',
                    'published_at' => now()->subDays($data['days_ago']),
                    'allow_comments' => true,
                    'cover_image_id' => $coverId,
                    'views_count' => random_int(40, 280),
                    'comments_count' => 0,
                ],
            );
            $count++;
        }

        $removed = Article::query()->whereNotIn('slug', $slugs)->forceDelete();

        $this->command?->info("✅ {$count} actualités synchronisées".($removed ? " ({$removed} anciennes retirées)" : '').'.');
    }

    private function ensureCoverMedia(string $filename, string $uploaderId, string $organizationId): ?string
    {
        $source = base_path('../frontend/public/brand/'.$filename);
        if (! is_file($source)) {
            $source = base_path('../../frontend/public/brand/'.$filename);
        }
        if (! is_file($source)) {
            return null;
        }

        $relative = 'news/covers/'.$filename;
        Storage::disk('public')->makeDirectory('news/covers');

        if (! Storage::disk('public')->exists($relative)) {
            File::copy($source, Storage::disk('public')->path($relative));
        }

        $media = Media::query()->updateOrCreate(
            [
                'disk' => 'public',
                'path' => $relative,
            ],
            [
                'uploaded_by' => $uploaderId,
                'collection' => 'article-covers',
                'name' => pathinfo($filename, PATHINFO_FILENAME),
                'file_name' => $filename,
                'mime_type' => mime_content_type($source) ?: 'image/jpeg',
                'size' => filesize($source) ?: 0,
                'metadata' => ['organization_id' => $organizationId],
            ],
        );

        return $media->id;
    }
}
