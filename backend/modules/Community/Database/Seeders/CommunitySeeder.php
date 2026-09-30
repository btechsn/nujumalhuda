<?php

namespace Modules\Community\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Community\Models\CommunityEvent;
use Modules\Community\Models\GalleryItem;
use Modules\Community\Models\Partner;
use Modules\Community\Models\Question;
use Modules\Community\Models\Testimonial;
use Modules\Core\Enums\OrganizationType;
use Modules\Core\Models\Organization;
use Modules\Core\Models\User;
use Modules\Dahira\Models\DahiraGroup;

class CommunitySeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::query()->where('email', 'admin@nujumalhuda.com')->first()
            ?? User::query()->first();

        if (!$admin) {
            $this->command?->warn('Aucun utilisateur pour CommunitySeeder.');

            return;
        }

        $questions = [
            [
                'slug_key' => 'tarawih',
                'question_i18n' => [
                    'fr' => 'Comment organiser les tarawih en famille quand on ne peut pas venir à la mosquée ?',
                    'en' => 'How can we organise tarawih at home when we cannot come to the mosque?',
                    'ar' => 'كيف ننظم التراويح في البيت إن تعذّر المجيء إلى المسجد؟',
                ],
                'answer_i18n' => [
                    'fr' => 'On peut prier en groupe à la maison, avec un imam de la famille, en gardant la simplicité et la présence du cœur.',
                    'en' => 'You may pray as a household group, with a family imam, keeping simplicity and presence of heart.',
                    'ar' => 'يُصلّى جماعة في البيت بإمام من الأهل، مع اليسر وحضور القلب.',
                ],
                'topics' => ['ramadan', 'priere', 'famille'],
            ],
            [
                'slug_key' => 'zakat-fitr',
                'question_i18n' => [
                    'fr' => 'Qui doit verser la zakat al-fitr dans un foyer, et avant quel moment ?',
                    'en' => 'Who must pay zakat al-fitr in a household, and before when?',
                    'ar' => 'من تؤدّى عنه زكاة الفطر في البيت، ومتى؟',
                ],
                'answer_i18n' => [
                    'fr' => 'Le chef de famille pour lui-même et ceux à sa charge, avant la prière de l’Aïd, selon le barème du muud annoncé.',
                    'en' => 'The head of household for himself and his dependents, before the Eid prayer, according to the announced mudd rate.',
                    'ar' => 'ربّ الأسرة عن نفسه وعمن يعول، قبل صلاة العيد، وفق المدّ المعلن.',
                ],
                'topics' => ['zakat', 'ramadan', 'fiqh'],
            ],
            [
                'slug_key' => 'inscription',
                'question_i18n' => [
                    'fr' => 'Quels documents faut-il pour inscrire un enfant aux programmes du centre ?',
                    'en' => 'Which documents are needed to enrol a child in the centre’s programmes?',
                    'ar' => 'ما الوثائق اللازمة لتسجيل طفل في برامج المركز؟',
                ],
                'answer_i18n' => [
                    'fr' => 'Une pièce d’identité du tuteur, l’acte de naissance de l’enfant, et le formulaire d’inscription en ligne ou au secrétariat.',
                    'en' => 'A guardian ID, the child’s birth certificate, and the enrolment form online or at the office.',
                    'ar' => 'هوية الولي وشهادة ميلاد الطفل واستمارة التسجيل عبر الإنترنت أو في الأمانة.',
                ],
                'topics' => ['inscription', 'famille', 'programmes'],
            ],
            [
                'slug_key' => 'dahira',
                'question_i18n' => [
                    'fr' => 'Comment rejoindre le dahira du centre et participer aux cotisations ?',
                    'en' => 'How do I join the centre’s dahira and take part in contributions?',
                    'ar' => 'كيف أنضم إلى دائرة المركز وأشارك في الاشتراكات؟',
                ],
                'answer_i18n' => [
                    'fr' => 'Demandez l’adhésion via la page Dahira ; un responsable valide votre demande, puis les échéances de cotisation apparaissent.',
                    'en' => 'Request membership on the Dahira page; an officer validates it, then contribution schedules appear.',
                    'ar' => 'اطلب الانضمام عبر صفحة الدائرة؛ يوافق مسؤول ثم تظهر جداول الاشتراك.',
                ],
                'topics' => ['dahira', 'communaute'],
            ],
        ];

        foreach ($questions as $index => $item) {
            $existing = Question::query()
                ->where('asker_id', $admin->id)
                ->where('question_i18n->fr', $item['question_i18n']['fr'])
                ->first();

            $recentAt = now()->subDays($index)->subHours($index);
            $payload = [
                'asker_id' => $admin->id,
                'teacher_id' => $admin->id,
                'question_i18n' => $item['question_i18n'],
                'answer_i18n' => $item['answer_i18n'],
                'topics' => $item['topics'],
                'status' => 'published',
                'is_public' => true,
                'answered_at' => $recentAt,
            ];

            if ($existing) {
                $existing->update($payload);
                $existing->created_at = $recentAt;
                $existing->save();
            } else {
                $created = Question::create($payload);
                $created->created_at = $recentAt;
                $created->save();
            }
        }

        $eventTitle = 'Hadara Jumma';
        $legacyTitle = 'Majlis du vendredi — familles';
        $event = CommunityEvent::query()
            ->where(function ($query) use ($eventTitle, $legacyTitle) {
                $query->where('title_i18n->fr', $eventTitle)
                    ->orWhere('title_i18n->fr', $legacyTitle);
            })
            ->first();
        $eventPayload = [
            'title_i18n' => [
                'fr' => $eventTitle,
                'en' => 'Hadara Jumma',
                'ar' => 'حضرة الجمعة',
            ],
            'description_i18n' => [
                'fr' => 'Hadara Jumma chaque vendredi à la zawiya : rappel, dhikr et rencontre de la communauté.',
                'en' => 'Hadara Jumma every Friday at the zawiya: reminder, dhikr and community gathering.',
                'ar' => 'حضرة الجمعة كل جمعة في الزاوية: تذكير وذكر ولقاء للمجتمع.',
            ],
            'starts_at' => now()->next('Friday')->setTime(15, 30),
            'ends_at' => now()->next('Friday')->setTime(17, 0),
            'location' => 'Zawiya Nujum Al-Huda',
            'capacity' => 120,
            'is_published' => true,
        ];
        if ($event) {
            $event->update($eventPayload);
        } else {
            CommunityEvent::create($eventPayload);
        }

        foreach ([
            [
                'author_name' => 'Aïssatou Ndiaye',
                'relation' => 'parent',
                'content_i18n' => [
                    'fr' => 'Nos enfants ont trouvé un cadre sérieux et bienveillant. La communauté du centre nous accompagne vraiment.',
                    'en' => 'Our children found a serious and caring setting. The centre’s community truly walks with us.',
                    'ar' => 'وجد أولادنا إطاراً جادّاً رحيماً. مجتمع المركز يرافقنا حقاً.',
                ],
            ],
            [
                'author_name' => 'Moussa Fall',
                'relation' => 'student',
                'content_i18n' => [
                    'fr' => 'Les discussions et le dahira m’ont aidé à rester lié au centre, même hors des cours.',
                    'en' => 'Discussions and the dahira helped me stay linked to the centre beyond classes.',
                    'ar' => 'النقاشات والدائرة أعاناني على الارتباط بالمركز خارج الدروس.',
                ],
            ],
            [
                'author_name' => 'Fatou Ba',
                'relation' => 'alumni',
                'content_i18n' => [
                    'fr' => 'J’ai appris le Coran ici, et je reviens encore pour les khutbas et la communauté. Le centre reste ma maison.',
                    'en' => 'I learned the Quran here, and I still come back for khutbas and community. The centre remains my home.',
                    'ar' => 'تعلّمت القرآن هنا، وما زلت أعود للخطب والمجتمع. المركز بيتي.',
                ],
            ],
            [
                'author_name' => 'Ibrahima Diop',
                'relation' => 'parent',
                'content_i18n' => [
                    'fr' => 'Les enseignants sont disponibles et les horaires clairs. On sent un vrai suivi pour chaque élève.',
                    'en' => 'The teachers are available and the schedule is clear. You feel real follow-up for every student.',
                    'ar' => 'المدرّسون متاحون والجداول واضحة. نحسّ بمتابعة حقيقية لكل تلميذ.',
                ],
            ],
            [
                'author_name' => 'Marième Sarr',
                'relation' => 'member',
                'content_i18n' => [
                    'fr' => 'La zawiya et les directs nous relient à la vie du centre, même quand on ne peut pas venir chaque jour.',
                    'en' => 'The zawiya and the lives keep us connected to the centre, even when we cannot come every day.',
                    'ar' => 'الزاوية والبثوث تربطنا بحياة المركز حتى حين لا نستطيع الحضور كل يوم.',
                ],
            ],
        ] as $row) {
            Testimonial::updateOrCreate(
                ['author_name' => $row['author_name']],
                [
                    'relation' => $row['relation'],
                    'content_i18n' => $row['content_i18n'],
                    'status' => 'approved',
                ]
            );
        }

        foreach ([
            [
                'name' => 'Association des Imams du Sénégal',
                'description_i18n' => [
                    'fr' => 'Partenaire pour la formation des prédicateurs et l’échange sur les pratiques mosquée.',
                    'en' => 'Partner for preacher training and exchange on mosque practices.',
                    'ar' => 'شريك لتكوين الدعاة وتبادل ممارسات المساجد.',
                ],
                'website_url' => 'https://example.com/imams',
                'logo_url' => '/brand/slide-priere.jpg',
                'sort_order' => 1,
            ],
            [
                'name' => 'Bibliothèque Islamique de Dakar',
                'description_i18n' => [
                    'fr' => 'Accès aux ouvrages et soutien aux ateliers de lecture pour les élèves du centre.',
                    'en' => 'Book access and support for reading workshops for centre students.',
                    'ar' => 'الوصول إلى الكتب ودعم ورش القراءة لتلاميذ المركز.',
                ],
                'website_url' => 'https://example.com/bib',
                'logo_url' => '/brand/slide-academique.jpg',
                'sort_order' => 2,
            ],
            [
                'name' => 'Radio Fréquence Islam',
                'description_i18n' => [
                    'fr' => 'Diffusion ponctuelle des khutbas et annonces du centre.',
                    'en' => 'Occasional broadcast of the centre’s khutbas and announcements.',
                    'ar' => 'بثّ عرضي لخطب وإعلانات المركز.',
                ],
                'website_url' => 'https://example.com/radio',
                'logo_url' => '/brand/slide-live.jpg',
                'sort_order' => 3,
            ],
            [
                'name' => 'Fondation Éducation & Coran',
                'description_i18n' => [
                    'fr' => 'Soutien aux bourses et au matériel pédagogique.',
                    'en' => 'Support for scholarships and teaching materials.',
                    'ar' => 'دعم المنح والوسائل التعليمية.',
                ],
                'website_url' => 'https://example.com/fondation',
                'logo_url' => '/brand/slide-centre.jpg',
                'sort_order' => 4,
            ],
            [
                'name' => 'Clinique solidaire Sud Foire',
                'description_i18n' => [
                    'fr' => 'Actions de santé communautaire avec les dahiras du quartier.',
                    'en' => 'Community health actions with local dahiras.',
                    'ar' => 'أعمال صحية مجتمعية مع دوائر الحي.',
                ],
                'website_url' => 'https://example.com/sante',
                'logo_url' => '/brand/slide-actualites.jpg',
                'sort_order' => 5,
            ],
            [
                'name' => 'Collectif des Zawiya de Dakar',
                'description_i18n' => [
                    'fr' => 'Coordination des hadaras et des rendez-vous spirituels partagés.',
                    'en' => 'Coordination of shared hadaras and spiritual gatherings.',
                    'ar' => 'تنسيق الحضرات واللقاءات الروحية المشتركة.',
                ],
                'website_url' => 'https://example.com/zawaya',
                'logo_url' => '/brand/slide-zawiya-soir.png',
                'sort_order' => 6,
            ],
            [
                'name' => 'Institut de Récitation Medina',
                'description_i18n' => [
                    'fr' => 'Ateliers de tajwīd et concours de mémorisation.',
                    'en' => 'Tajwīd workshops and memorization contests.',
                    'ar' => 'ورش التجويد ومسابقات الحفظ.',
                ],
                'website_url' => 'https://example.com/recitation',
                'logo_url' => '/brand/slide-recitation.jpg',
                'sort_order' => 7,
            ],
            [
                'name' => 'Maison de l’Édition Islamique',
                'description_i18n' => [
                    'fr' => 'Fourniture de livres et fascicules pour les classes.',
                    'en' => 'Books and booklets for the classrooms.',
                    'ar' => 'توفير الكتب والكراسات للفصول.',
                ],
                'website_url' => 'https://example.com/edition',
                'logo_url' => '/brand/slide-replay.jpg',
                'sort_order' => 8,
            ],
            [
                'name' => 'Association des Parents d’Élèves',
                'description_i18n' => [
                    'fr' => 'Relais entre les familles et l’administration du centre.',
                    'en' => 'Link between families and the centre administration.',
                    'ar' => 'جسر بين الأسر وإدارة المركز.',
                ],
                'website_url' => 'https://example.com/ape',
                'logo_url' => '/brand/slide-quizz.jpg',
                'sort_order' => 9,
            ],
            [
                'name' => 'Comité des Hadaras Régionales',
                'description_i18n' => [
                    'fr' => 'Organisation des grands rassemblements spirituels.',
                    'en' => 'Organisation of major spiritual gatherings.',
                    'ar' => 'تنظيم التجمعات الروحية الكبرى.',
                ],
                'website_url' => 'https://example.com/hadaras',
                'logo_url' => '/brand/slide-zawiya-cour.png',
                'sort_order' => 10,
            ],
            [
                'name' => 'Centre de Formation Arabe',
                'description_i18n' => [
                    'fr' => 'Cours d’arabe et échanges pédagogiques avec nos enseignants.',
                    'en' => 'Arabic courses and teaching exchanges with our teachers.',
                    'ar' => 'دورات عربية وتبادل تربوي مع معلمينا.',
                ],
                'website_url' => 'https://example.com/arabe',
                'logo_url' => '/brand/slide-zawiya-mihrab.png',
                'sort_order' => 11,
            ],
            [
                'name' => 'Solidarité Quartier Foire',
                'description_i18n' => [
                    'fr' => 'Aide alimentaire et accompagnement social du voisinage.',
                    'en' => 'Food aid and social support for the neighbourhood.',
                    'ar' => 'مساعدة غذائية ومرافقة اجتماعية للحي.',
                ],
                'website_url' => 'https://example.com/solidarite',
                'logo_url' => '/brand/intro-lecon.jpg',
                'sort_order' => 12,
            ],
        ] as $row) {
            Partner::updateOrCreate(
                ['name' => $row['name']],
                [
                    'description_i18n' => $row['description_i18n'],
                    'website_url' => $row['website_url'],
                    'logo_url' => $row['logo_url'],
                    'sort_order' => $row['sort_order'],
                    'is_active' => true,
                ]
            );
        }

        $extraGroups = [
            [
                'slug' => 'dahira-jeunes-nujum',
                'name' => [
                    'fr' => 'Dahira des jeunes',
                    'en' => 'Youth dahira',
                    'ar' => 'دائرة الشباب',
                ],
                'description' => [
                    'fr' => 'Cercle des jeunes du centre : lecture, service et sorties.',
                    'en' => 'Youth circle of the centre: reading, service and outings.',
                    'ar' => 'حلقة شباب المركز: قراءة وخدمة وخرجات.',
                ],
                'weekday' => 6,
            ],
            [
                'slug' => 'dahira-femmes-nujum',
                'name' => [
                    'fr' => 'Dahira des sœurs',
                    'en' => 'Sisters’ dahira',
                    'ar' => 'دائرة الأخوات',
                ],
                'description' => [
                    'fr' => 'Espace des sœurs : formation, entraide et préparation du Ramadan.',
                    'en' => 'Sisters’ space: training, mutual aid and Ramadan preparation.',
                    'ar' => 'فضاء الأخوات: تكوين وتعاون واستعداد لرمضان.',
                ],
                'weekday' => 3,
            ],
        ];

        foreach ($extraGroups as $groupData) {
            $organization = Organization::query()->where('slug', $groupData['slug'])->first() ?? new Organization();
            $organization->type = OrganizationType::DAHIRA;
            $organization->slug = $groupData['slug'];
            $organization->country = 'SN';
            $organization->city = 'Dakar';
            $organization->is_active = true;
            $organization->setTranslations('name', $groupData['name']);
            $organization->save();

            DahiraGroup::updateOrCreate(
                ['organization_id' => $organization->id],
                [
                    'name_i18n' => $groupData['name'],
                    'description_i18n' => $groupData['description'],
                    'meeting_weekday' => $groupData['weekday'],
                    'location' => 'Institut Nujum Al-Huda, Dakar',
                    'is_active' => true,
                ]
            );
        }

        // Ensure the main dahira from DahiraSeeder exists too.
        $this->call(\Modules\Dahira\Database\Seeders\DahiraSeeder::class);

        foreach ([
            [
                'kind' => 'photo',
                'event_name' => 'Hadara Jumma',
                'media_url' => '/brand/slide-zawiya-soir.png',
                'caption_i18n' => [
                    'fr' => 'Hadara du vendredi à la zawiya',
                    'en' => 'Friday hadara at the zawiya',
                    'ar' => 'حضرة الجمعة في الزاوية',
                ],
                'taken_on' => now()->subDays(3)->toDateString(),
            ],
            [
                'kind' => 'photo',
                'event_name' => 'Ouverture de l’année scolaire',
                'media_url' => '/brand/slide-academique.jpg',
                'caption_i18n' => [
                    'fr' => 'Cérémonie d’ouverture des cours',
                    'en' => 'Opening ceremony of classes',
                    'ar' => 'حفل افتتاح الدروس',
                ],
                'taken_on' => now()->subDays(20)->toDateString(),
            ],
            [
                'kind' => 'photo',
                'event_name' => 'Prière du vendredi',
                'media_url' => '/brand/slide-zawiya-mihrab.png',
                'caption_i18n' => [
                    'fr' => 'Ambiance de la zawiya',
                    'en' => 'Atmosphere of the zawiya',
                    'ar' => 'أجواء الزاوية',
                ],
                'taken_on' => now()->subDays(10)->toDateString(),
            ],
            [
                'kind' => 'video',
                'event_name' => 'Khutba du vendredi',
                'media_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                'thumbnail_url' => '/brand/slide-priere.jpg',
                'caption_i18n' => [
                    'fr' => 'Extrait de la khutba',
                    'en' => 'Excerpt from the khutba',
                    'ar' => 'مقتطف من الخطبة',
                ],
                'taken_on' => now()->subDays(7)->toDateString(),
            ],
            [
                'kind' => 'video',
                'event_name' => 'Leçon de Coran',
                'media_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                'thumbnail_url' => '/brand/intro-lecon.jpg',
                'caption_i18n' => [
                    'fr' => 'Cours filé en studio',
                    'en' => 'Studio lesson recording',
                    'ar' => 'تسجيل درس في الاستوديو',
                ],
                'taken_on' => now()->subDays(14)->toDateString(),
            ],
            [
                'kind' => 'photo',
                'event_name' => 'Gamou',
                'media_url' => '/brand/slide-actualites.jpg',
                'caption_i18n' => [
                    'fr' => 'Temps fort du Gamou au centre',
                    'en' => 'Highlight of Gamou at the centre',
                    'ar' => 'لحظة مميزة من الغامو في المركز',
                ],
                'taken_on' => now()->subDays(40)->toDateString(),
            ],
        ] as $row) {
            GalleryItem::updateOrCreate(
                [
                    'kind' => $row['kind'],
                    'event_name' => $row['event_name'],
                    'media_url' => $row['media_url'],
                ],
                [
                    'caption_i18n' => $row['caption_i18n'],
                    'thumbnail_url' => $row['thumbnail_url'] ?? null,
                    'taken_on' => $row['taken_on'],
                    'is_public' => true,
                ]
            );
        }

        $this->command?->info('✅ Communauté synchronisée.');
    }
}
