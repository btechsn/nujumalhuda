<?php

namespace Modules\Education\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Core\Models\Organization;
use Modules\Core\Models\User;
use Modules\Education\Models\Teacher;

class TeacherSeeder extends Seeder
{
    public function run(): void
    {
        $organizationId = Organization::query()->where('slug', 'nujum-al-huda')->value('id');

        if (! $organizationId) {
            $this->command?->warn('Organisation nujum-al-huda introuvable. TeacherSeeder ignoré.');

            return;
        }

        $teachers = [
            [
                'user' => [
                    'first_name' => 'Cheikh Abdoulaye',
                    'last_name' => 'Diop',
                    'email' => 'a.diop@nujumalhuda.com',
                ],
                'title_i18n' => [
                    'fr' => 'Enseignant de Coran et Tajwid',
                    'en' => 'Quran and Tajweed teacher',
                    'ar' => 'مدرس القرآن والتجويد',
                ],
                'photo' => '/brand/teachers/diop.jpg',
                'bio_i18n' => [
                    'fr' => 'Spécialiste en mémorisation du Coran et Tajwid avec plus de 15 ans d\'expérience. Diplômé de l\'Université Al-Azhar du Caire.',
                    'en' => 'Specialist in Quran memorization and Tajweed with over 15 years of experience. Graduate of Al-Azhar University in Cairo.',
                    'ar' => 'متخصص في حفظ القرآن والتجويد مع أكثر من 15 عامًا من الخبرة. خريج جامعة الأزهر بالقاهرة.',
                ],
                'specialties_i18n' => [
                    'fr' => ['Tajwid', 'Mémorisation du Coran', 'Qira\'at'],
                    'en' => ['Tajweed', 'Quran Memorization', 'Qira\'at'],
                    'ar' => ['التجويد', 'حفظ القرآن', 'القراءات'],
                ],
                'qualifications_i18n' => [
                    'fr' => [
                        'Licence en études coraniques — Al-Azhar (Le Caire)',
                        'Diplôme de Tajwid — Institut Dar Al-Quran',
                        'Enseignant au centre Nujum Al-Huda depuis 2015',
                        'Formateur de récitateurs (Hafs ʿan ʿĀṣim)',
                    ],
                    'en' => [
                        'Bachelor in Quranic Studies — Al-Azhar (Cairo)',
                        'Tajweed Diploma — Dar Al-Quran Institute',
                        'Teacher at Nujum Al-Huda since 2015',
                        'Trainer of reciters (Hafs ʿan ʿĀṣim)',
                    ],
                    'ar' => [
                        'بكالوريوس في الدراسات القرآنية — الأزهر',
                        'دبلوم التجويد — معهد دار القرآن',
                        'أستاذ بمركز نجوم الهدى منذ 2015',
                        'مدرب قراء (حفص عن عاصم)',
                    ],
                ],
                'has_ijaza' => true,
                'sanad' => [
                    'riwaya' => 'Hafs ʿan ʿĀṣim',
                    'chain' => [
                        'Cheikh Abdoulaye Diop',
                        'Cheikh Muhammad ibn Ahmad (Al-Azhar)',
                        'Cheikh Ali al-Hudhayfi',
                        '… chaîne menant au Prophète ﷺ',
                    ],
                ],
                'ijaza_details' => [
                    'fr' => [
                        'title' => 'Ijaza en récitation Hafs ʿan ʿĀṣim',
                        'issuer' => 'Cheikh Muhammad ibn Ahmad — Al-Azhar',
                        'year' => '2009',
                        'domain' => 'Tajwid et mémorisation complète',
                    ],
                    'en' => [
                        'title' => 'Ijaza in Hafs ʿan ʿĀṣim recitation',
                        'issuer' => 'Shaykh Muhammad ibn Ahmad — Al-Azhar',
                        'year' => '2009',
                        'domain' => 'Tajweed and full memorization',
                    ],
                    'ar' => [
                        'title' => 'إجازة في رواية حفص عن عاصم',
                        'issuer' => 'الشيخ محمد بن أحمد — الأزهر',
                        'year' => '2009',
                        'domain' => 'التجويد والحفظ الكامل',
                    ],
                ],
                'students_count' => 180,
                'courses_taught' => 12,
                'total_sessions' => 640,
                'is_available' => true,
                'is_featured' => true,
                'display_order' => 1,
            ],
            [
                'user' => [
                    'first_name' => 'Ousmane',
                    'last_name' => 'Seck',
                    'email' => 'o.seck@nujumalhuda.com',
                ],
                'title_i18n' => [
                    'fr' => 'Enseignant de langue arabe',
                    'en' => 'Arabic language teacher',
                    'ar' => 'مدرس اللغة العربية',
                ],
                'photo' => '/brand/teachers/seck.jpg',
                'bio_i18n' => [
                    'fr' => 'Enseignant de langue arabe et grammaire. Spécialisé dans le Nahw et le Sarf avec une approche pédagogique claire.',
                    'en' => 'Arabic language and grammar teacher. Specialized in Nahw and Sarf with a clear pedagogical approach.',
                    'ar' => 'مدرس اللغة العربية والنحو. متخصص في النحو والصرف بمنهج تربوي واضح.',
                ],
                'specialties_i18n' => [
                    'fr' => ['Grammaire arabe (Nahw)', 'Morphologie (Sarf)', 'Expression écrite'],
                    'en' => ['Arabic Grammar (Nahw)', 'Morphology (Sarf)', 'Written Expression'],
                    'ar' => ['النحو', 'الصرف', 'التعبير الكتابي'],
                ],
                'qualifications_i18n' => [
                    'fr' => [
                        'Master en langue arabe — Université Cheikh Anta Diop',
                        'Formation pédagogique — ENS',
                        '10 ans d\'enseignement de l\'arabe littéraire',
                        'Responsable du parcours Arabe débutant / intermédiaire',
                    ],
                    'en' => [
                        'Master in Arabic Language — UCAD',
                        'Pedagogical training — ENS',
                        '10 years teaching classical Arabic',
                        'Lead for Beginner / Intermediate Arabic tracks',
                    ],
                    'ar' => [
                        'ماجستير في اللغة العربية — جامعة دكار',
                        'تدريب تربوي — ENS',
                        '10 سنوات في تدريس العربية الفصحى',
                        'مسؤول مسار العربية للمبتدئين والمتوسطين',
                    ],
                ],
                'has_ijaza' => false,
                'sanad' => null,
                'ijaza_details' => null,
                'students_count' => 95,
                'courses_taught' => 8,
                'total_sessions' => 320,
                'is_available' => true,
                'is_featured' => true,
                'display_order' => 2,
            ],
            [
                'user' => [
                    'first_name' => 'Serigne Fallou',
                    'last_name' => 'Mbacké',
                    'email' => 's.mbacke@nujumalhuda.com',
                ],
                'title_i18n' => [
                    'fr' => 'Spécialiste Baye Niasse & sciences islamiques',
                    'en' => 'Baye Niasse & Islamic sciences specialist',
                    'ar' => 'متخصص في باي نياس والعلوم الإسلامية',
                ],
                'photo' => '/brand/teachers/mbacke.jpg',
                'bio_i18n' => [
                    'fr' => 'Disciple et spécialiste des œuvres de Cheikh Ibrahim Niasse. Expert en soufisme et en sciences islamiques traditionnelles.',
                    'en' => 'Disciple and specialist of the works of Sheikh Ibrahim Niasse. Expert in Sufism and traditional Islamic sciences.',
                    'ar' => 'تلميذ ومتخصص في أعمال الشيخ إبراهيم نياس. خبير في التصوف والعلوم الإسلامية التقليدية.',
                ],
                'specialties_i18n' => [
                    'fr' => ['Œuvres de Baye Niasse', 'Soufisme', 'Fiqh Maliki'],
                    'en' => ['Works of Baye Niasse', 'Sufism', 'Maliki Fiqh'],
                    'ar' => ['أعمال باي نياس', 'التصوف', 'الفقه المالكي'],
                ],
                'qualifications_i18n' => [
                    'fr' => [
                        'Formation traditionnelle en sciences islamiques',
                        'Études approfondies des œuvres de Baye Niasse',
                        '20 ans d\'enseignement et d\'accompagnement',
                        'Intervenant régulier à la zawiya du centre',
                    ],
                    'en' => [
                        'Traditional training in Islamic sciences',
                        'In-depth study of Baye Niasse\'s works',
                        '20 years of teaching and mentorship',
                        'Regular speaker at the centre zawiya',
                    ],
                    'ar' => [
                        'تدريب تقليدي في العلوم الإسلامية',
                        'دراسات معمقة لأعمال باي نياس',
                        '20 عامًا من التدريس والإرشاد',
                        'محاضر منتظم في زاوية المركز',
                    ],
                ],
                'has_ijaza' => true,
                'sanad' => [
                    'riwaya' => 'Transmission spirituelle et savante',
                    'chain' => [
                        'Serigne Fallou Mbacké',
                        'Cheikh Ibrahim Niasse (Baye Niasse) — héritage étudié',
                        'Chaîne des savants tidjanes du Sénégal',
                    ],
                ],
                'ijaza_details' => [
                    'fr' => [
                        'title' => 'Ijaza dans la lecture des œuvres de Baye Niasse',
                        'issuer' => 'Chaîne des disciples tidjanes — Kaolack',
                        'year' => '2004',
                        'domain' => 'Sciences islamiques et enseignement soufi',
                    ],
                    'en' => [
                        'title' => 'Ijaza in reading the works of Baye Niasse',
                        'issuer' => 'Tijani disciples chain — Kaolack',
                        'year' => '2004',
                        'domain' => 'Islamic sciences and Sufi teaching',
                    ],
                    'ar' => [
                        'title' => 'إجازة في قراءة أعمال باي نياس',
                        'issuer' => 'سلسلة تلاميذ التجانية — كاولاك',
                        'year' => '2004',
                        'domain' => 'العلوم الإسلامية والتعليم الصوفي',
                    ],
                ],
                'students_count' => 140,
                'courses_taught' => 6,
                'total_sessions' => 410,
                'is_available' => true,
                'is_featured' => true,
                'display_order' => 3,
            ],
            [
                'user' => [
                    'first_name' => 'Aïcha',
                    'last_name' => 'Ndiaye',
                    'email' => 'a.ndiaye@nujumalhuda.com',
                ],
                'title_i18n' => [
                    'fr' => 'Enseignante Coran — enfants & femmes',
                    'en' => 'Quran teacher — children & women',
                    'ar' => 'معلمة القرآن — للأطفال والنساء',
                ],
                'photo' => '/brand/teachers/ndiaye.jpg',
                'bio_i18n' => [
                    'fr' => 'Enseignante spécialisée dans l\'enseignement du Coran aux enfants et aux femmes. Diplômée en sciences islamiques.',
                    'en' => 'Teacher specialized in teaching Quran to children and women. Graduate in Islamic sciences.',
                    'ar' => 'معلمة متخصصة في تدريس القرآن للأطفال والنساء. خريجة العلوم الإسلامية.',
                ],
                'specialties_i18n' => [
                    'fr' => ['Enseignement du Coran aux enfants', 'Tajwid de base', 'Éducation islamique'],
                    'en' => ['Teaching Quran to children', 'Basic Tajweed', 'Islamic Education'],
                    'ar' => ['تدريس القرآن للأطفال', 'التجويد الأساسي', 'التربية الإسلامية'],
                ],
                'qualifications_i18n' => [
                    'fr' => [
                        'Licence en études islamiques',
                        'Formation en pédagogie de l\'enfant',
                        'Certificat de Tajwid',
                        '8 ans d\'animation de cercles de lecture',
                    ],
                    'en' => [
                        'Bachelor in Islamic Studies',
                        'Training in child pedagogy',
                        'Tajweed Certificate',
                        '8 years leading reading circles',
                    ],
                    'ar' => [
                        'بكالوريوس في الدراسات الإسلامية',
                        'تدريب في تربية الأطفال',
                        'شهادة التجويد',
                        '8 سنوات في حلقات القراءة',
                    ],
                ],
                'has_ijaza' => false,
                'sanad' => null,
                'ijaza_details' => null,
                'students_count' => 110,
                'courses_taught' => 5,
                'total_sessions' => 280,
                'is_available' => true,
                'is_featured' => true,
                'display_order' => 4,
            ],
            [
                'user' => [
                    'first_name' => 'Mamadou',
                    'last_name' => 'Ba',
                    'email' => 'm.ba@nujumalhuda.com',
                ],
                'title_i18n' => [
                    'fr' => 'Enseignant de fiqh et hadith',
                    'en' => 'Fiqh and Hadith teacher',
                    'ar' => 'مدرس الفقه والحديث',
                ],
                'photo' => '/brand/teachers/ba.jpg',
                'bio_i18n' => [
                    'fr' => 'Enseignant du fiqh malikite et des hadiths authentiques. Formé à la lecture des textes classiques et à leur application quotidienne.',
                    'en' => 'Teacher of Maliki fiqh and authentic hadiths. Trained in classical texts and their daily application.',
                    'ar' => 'مدرس الفقه المالكي والأحاديث الصحيحة. مكوّن في قراءة النصوص وتطبيقها يوميًا.',
                ],
                'specialties_i18n' => [
                    'fr' => ['Fiqh Maliki', 'Hadith', 'Aqida'],
                    'en' => ['Maliki Fiqh', 'Hadith', 'Aqida'],
                    'ar' => ['الفقه المالكي', 'الحديث', 'العقيدة'],
                ],
                'qualifications_i18n' => [
                    'fr' => [
                        'Formation en sciences islamiques — Mauritanie',
                        'Étude du Muwatta et des textes malikites',
                        '12 ans d\'enseignement du fiqh',
                        'Animateur de cercles de hadith au centre',
                    ],
                    'en' => [
                        'Islamic sciences training — Mauritania',
                        'Study of the Muwatta and Maliki texts',
                        '12 years teaching fiqh',
                        'Hadith circle facilitator at the centre',
                    ],
                    'ar' => [
                        'تكوين في العلوم الإسلامية — موريتانيا',
                        'دراسة الموطأ والنصوص المالكية',
                        '12 سنة في تدريس الفقه',
                        'منسق حلقات الحديث في المركز',
                    ],
                ],
                'has_ijaza' => true,
                'sanad' => [
                    'riwaya' => 'Transmission malikite',
                    'chain' => [
                        'Mamadou Ba',
                        'Cheikh Mohamed Lemine (Nouakchott)',
                        'Chaîne des savants malikites de Mauritanie',
                    ],
                ],
                'ijaza_details' => [
                    'fr' => [
                        'title' => 'Ijaza en fiqh malikite',
                        'issuer' => 'Cheikh Mohamed Lemine — Nouakchott',
                        'year' => '2012',
                        'domain' => 'Fiqh et hadith',
                    ],
                    'en' => [
                        'title' => 'Ijaza in Maliki fiqh',
                        'issuer' => 'Shaykh Mohamed Lemine — Nouakchott',
                        'year' => '2012',
                        'domain' => 'Fiqh and Hadith',
                    ],
                    'ar' => [
                        'title' => 'إجازة في الفقه المالكي',
                        'issuer' => 'الشيخ محمد الأمين — نواكشوط',
                        'year' => '2012',
                        'domain' => 'الفقه والحديث',
                    ],
                ],
                'students_count' => 75,
                'courses_taught' => 4,
                'total_sessions' => 210,
                'is_available' => true,
                'is_featured' => false,
                'display_order' => 5,
            ],
            [
                'user' => [
                    'first_name' => 'Khady',
                    'last_name' => 'Sarr',
                    'email' => 'k.sarr@nujumalhuda.com',
                ],
                'title_i18n' => [
                    'fr' => 'Enseignante d\'arabe & initiation',
                    'en' => 'Arabic & beginners teacher',
                    'ar' => 'معلمة العربية والمبتدئين',
                ],
                'photo' => '/brand/teachers/sarr.jpg',
                'bio_i18n' => [
                    'fr' => 'Accompagne les débutants à lire l\'arabe et à suivre les cours du centre avec confiance. Approche douce et progressive.',
                    'en' => 'Helps beginners read Arabic and follow centre classes with confidence. Gentle, progressive approach.',
                    'ar' => 'ترافق المبتدئين لقراءة العربية ومتابعة دروس المركز بثقة. نهج لطيف وتدريجي.',
                ],
                'specialties_i18n' => [
                    'fr' => ['Alphabet arabe', 'Lecture guidée', 'Initiation femmes'],
                    'en' => ['Arabic alphabet', 'Guided reading', 'Women beginners'],
                    'ar' => ['الحروف العربية', 'القراءة الموجهة', 'مبتدئات'],
                ],
                'qualifications_i18n' => [
                    'fr' => [
                        'Licence en lettres arabes',
                        'Formation en alphabétisation arabe',
                        '6 ans d\'ateliers pour débutants',
                        'Coordinatrice des cercles femmes',
                    ],
                    'en' => [
                        'Bachelor in Arabic Letters',
                        'Arabic literacy training',
                        '6 years of beginner workshops',
                        'Coordinator of women\'s circles',
                    ],
                    'ar' => [
                        'بكالوريوس في الآداب العربية',
                        'تدريب محو الأمية العربية',
                        '6 سنوات من ورش المبتدئين',
                        'منسقة حلقات النساء',
                    ],
                ],
                'has_ijaza' => false,
                'sanad' => null,
                'ijaza_details' => null,
                'students_count' => 88,
                'courses_taught' => 3,
                'total_sessions' => 190,
                'is_available' => true,
                'is_featured' => false,
                'display_order' => 6,
            ],
        ];

        $count = 0;

        foreach ($teachers as $teacherData) {
            $userData = $teacherData['user'];
            $titleI18n = $teacherData['title_i18n'] ?? null;
            $photo = $teacherData['photo'] ?? null;
            unset($teacherData['user'], $teacherData['title_i18n'], $teacherData['photo']);

            $user = User::query()->where('email', $userData['email'])->first();

            if (! $user) {
                $user = User::create([
                    'first_name' => $userData['first_name'],
                    'last_name' => $userData['last_name'],
                    'email' => $userData['email'],
                    'password' => 'password',
                    'locale' => 'fr',
                    'timezone' => 'Africa/Dakar',
                    'email_verified_at' => now(),
                ]);
            } else {
                $user->update([
                    'first_name' => $userData['first_name'],
                    'last_name' => $userData['last_name'],
                ]);
            }

            $payload = array_merge($teacherData, [
                'organization_id' => $organizationId,
                'availability' => array_filter([
                    'title_i18n' => $titleI18n,
                    'photo' => $photo,
                ]),
            ]);

            $teacher = Teacher::withTrashed()->find($user->id);

            if ($teacher) {
                if ($teacher->trashed()) {
                    $teacher->restore();
                }
                $teacher->update($payload);
            } else {
                Teacher::create(array_merge([
                    'id' => $user->id,
                ], $payload));
            }

            $count++;
        }

        $this->command?->info("✅ {$count} enseignants synchronisés.");
    }
}
