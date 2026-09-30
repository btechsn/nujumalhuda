<?php

namespace Modules\Education\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Modules\Core\Models\Organization;
use Modules\Education\Models\Program;

class ProgramSeeder extends Seeder
{
    public function run(): void
    {
        $organizationId = Organization::query()->where('slug', 'nujum-al-huda')->value('id');

        if (! $organizationId) {
            $this->command?->warn('Organisation nujum-al-huda introuvable. ProgramSeeder ignoré.');

            return;
        }

        $programs = [
            [
                'code' => 'CORAN-DEB',
                'name_i18n' => [
                    'fr' => 'Mémorisation du Coran — Débutant',
                    'en' => 'Quran Memorization — Beginner',
                    'ar' => 'حفظ القرآن الكريم — مبتدئ',
                ],
                'description_i18n' => [
                    'fr' => 'Programme d\'apprentissage et de mémorisation du Coran avec tajwid pour débutants. Les élèves commencent par Juz Amma et progressent progressivement.',
                    'en' => 'Quran learning and memorization with tajweed for beginners. Students start with Juz Amma and progress gradually.',
                    'ar' => 'برنامج تعلم وحفظ القرآن مع التجويد للمبتدئين. يبدأ الطلاب بجزء عم ويتقدمون تدريجياً.',
                ],
                'objectives_i18n' => [
                    'fr' => [
                        'Mémoriser Juz Amma avec une récitation correcte',
                        'Maîtriser les règles de base du tajwid',
                        'Développer une relation régulière avec le Coran',
                    ],
                    'en' => [
                        'Memorize Juz Amma with correct recitation',
                        'Master basic tajweed rules',
                        'Build a regular relationship with the Quran',
                    ],
                    'ar' => [
                        'حفظ جزء عم بتلاوة صحيحة',
                        'إتقان قواعد التجويد الأساسية',
                        'بناء علاقة منتظمة مع القرآن',
                    ],
                ],
                'type' => 'coran',
                'level' => 'debutant',
                'duration_weeks' => 52,
                'hours_per_week' => 10,
                'tuition_amount_minor' => 150000,
                'registration_amount_minor' => 25000,
                'min_age' => 7,
                'max_age' => 15,
                'is_active' => true,
                'is_featured' => true,
                'display_order' => 1,
            ],
            [
                'code' => 'CORAN-INT',
                'name_i18n' => [
                    'fr' => 'Mémorisation du Coran — Intermédiaire',
                    'en' => 'Quran Memorization — Intermediate',
                    'ar' => 'حفظ القرآن الكريم — متوسط',
                ],
                'description_i18n' => [
                    'fr' => 'Poursuite de la mémorisation après Juz Amma : renforcement du tajwid, révision structurée et progression vers de nouveaux ajza.',
                    'en' => 'Continued memorization after Juz Amma: stronger tajweed, structured revision and progress through further ajza.',
                    'ar' => 'متابعة الحفظ بعد جزء عم مع تعزيز التجويد والمراجعة المنظمة والتقدم في أجزاء جديدة.',
                ],
                'objectives_i18n' => [
                    'fr' => [
                        'Consolider la mémorisation déjà acquise',
                        'Approfondir le tajwid et le murattal',
                        'Mémoriser de nouveaux ajza avec révision hebdomadaire',
                    ],
                    'en' => [
                        'Consolidate existing memorization',
                        'Deepen tajweed and murattal',
                        'Memorize new ajza with weekly revision',
                    ],
                    'ar' => [
                        'ترسيخ المحفوظ السابق',
                        'تعميق التجويد والترتيل',
                        'حفظ أجزاء جديدة مع مراجعة أسبوعية',
                    ],
                ],
                'type' => 'coran',
                'level' => 'intermediaire',
                'duration_weeks' => 52,
                'hours_per_week' => 12,
                'tuition_amount_minor' => 160000,
                'registration_amount_minor' => 25000,
                'min_age' => 10,
                'max_age' => 18,
                'is_active' => true,
                'is_featured' => true,
                'display_order' => 2,
            ],
            [
                'code' => 'TAJWID-INIT',
                'name_i18n' => [
                    'fr' => 'Initiation au Tajwid',
                    'en' => 'Introduction to Tajweed',
                    'ar' => 'مقدمة في التجويد',
                ],
                'description_i18n' => [
                    'fr' => 'Apprentissage des règles de récitation du Coran pour débutants. Cours pratiques avec récitation guidée.',
                    'en' => 'Learning Quran recitation rules for beginners. Practical classes with guided recitation.',
                    'ar' => 'تعلم قواعد التلاوة للمبتدئين مع دروس عملية وتلاوة موجّهة.',
                ],
                'objectives_i18n' => [
                    'fr' => [
                        'Connaître les makharij et les sifates de base',
                        'Appliquer les règles de madd et d\'idgham',
                        'Réciter correctement des sourates courtes',
                    ],
                    'en' => [
                        'Know basic makharij and sifaat',
                        'Apply madd and idgham rules',
                        'Recite short surahs correctly',
                    ],
                    'ar' => [
                        'معرفة المخارج والصفات الأساسية',
                        'تطبيق أحكام المد والإدغام',
                        'تلاوة سور قصيرة بشكل صحيح',
                    ],
                ],
                'type' => 'coran',
                'level' => 'debutant',
                'duration_weeks' => 20,
                'hours_per_week' => 4,
                'tuition_amount_minor' => 60000,
                'registration_amount_minor' => 10000,
                'min_age' => 10,
                'is_active' => true,
                'is_featured' => false,
                'display_order' => 3,
            ],
            [
                'code' => 'ARABE-DEB',
                'name_i18n' => [
                    'fr' => 'Langue Arabe — Débutant',
                    'en' => 'Arabic Language — Beginner',
                    'ar' => 'اللغة العربية — مبتدئ',
                ],
                'description_i18n' => [
                    'fr' => 'Premiers pas en langue arabe : alphabet, lecture, vocabulaire utile et expressions du quotidien pour suivre les cours du centre.',
                    'en' => 'First steps in Arabic: alphabet, reading, useful vocabulary and everyday expressions to follow centre classes.',
                    'ar' => 'خطوات أولى في العربية: الحروف والقراءة والمفردات والتعابير اليومية لمتابعة دروس المركز.',
                ],
                'objectives_i18n' => [
                    'fr' => [
                        'Lire l\'alphabet arabe avec aisance',
                        'Comprendre des phrases simples',
                        'Acquérir un vocabulaire de base lié à la pratique religieuse',
                    ],
                    'en' => [
                        'Read the Arabic alphabet fluently',
                        'Understand simple sentences',
                        'Acquire basic vocabulary linked to religious practice',
                    ],
                    'ar' => [
                        'قراءة الحروف العربية بطلاقة',
                        'فهم جمل بسيطة',
                        'اكتساب مفردات أساسية مرتبطة بالممارسة الدينية',
                    ],
                ],
                'type' => 'arabe',
                'level' => 'debutant',
                'duration_weeks' => 32,
                'hours_per_week' => 6,
                'tuition_amount_minor' => 90000,
                'registration_amount_minor' => 15000,
                'min_age' => 8,
                'is_active' => true,
                'is_featured' => false,
                'display_order' => 4,
            ],
            [
                'code' => 'ARABE-INT',
                'name_i18n' => [
                    'fr' => 'Langue Arabe — Intermédiaire',
                    'en' => 'Arabic Language — Intermediate',
                    'ar' => 'اللغة العربية — متوسط',
                ],
                'description_i18n' => [
                    'fr' => 'Apprentissage de l\'arabe classique avec focus sur la grammaire (Nahw) et la morphologie (Sarf). Cours théoriques et pratiques.',
                    'en' => 'Classical Arabic with focus on grammar (Nahw) and morphology (Sarf). Theoretical and practical courses.',
                    'ar' => 'تعلم العربية الفصحى مع التركيز على النحو والصرف. دروس نظرية وعملية.',
                ],
                'objectives_i18n' => [
                    'fr' => [
                        'Maîtriser les bases du nahw et du sarf',
                        'Lire et comprendre des textes simples',
                        'S\'exprimer à l\'oral dans un registre correct',
                    ],
                    'en' => [
                        'Master basics of nahw and sarf',
                        'Read and understand simple texts',
                        'Speak in a correct register',
                    ],
                    'ar' => [
                        'إتقان أساسيات النحو والصرف',
                        'قراءة وفهم نصوص بسيطة',
                        'التعبير الشفهي بأسلوب صحيح',
                    ],
                ],
                'type' => 'arabe',
                'level' => 'intermediaire',
                'duration_weeks' => 40,
                'hours_per_week' => 8,
                'tuition_amount_minor' => 120000,
                'registration_amount_minor' => 20000,
                'min_age' => 12,
                'is_active' => true,
                'is_featured' => true,
                'display_order' => 5,
            ],
            [
                'code' => 'BAYE-OEUVRES',
                'name_i18n' => [
                    'fr' => 'Œuvres de Cheikh Ibrahim Niasse',
                    'en' => 'Works of Sheikh Ibrahim Niasse',
                    'ar' => 'أعمال الشيخ إبراهيم نياس',
                ],
                'description_i18n' => [
                    'fr' => 'Étude approfondie des œuvres et enseignements de Cheikh Ibrahim Niasse (Baye Niasse), figure majeure du soufisme au Sénégal.',
                    'en' => 'In-depth study of the works and teachings of Sheikh Ibrahim Niasse (Baye Niasse), a major figure of Sufism in Senegal.',
                    'ar' => 'دراسة معمقة لأعمال وتعاليم الشيخ إبراهيم نياس، الشخصية البارزة في التصوف بالسنغال.',
                ],
                'objectives_i18n' => [
                    'fr' => [
                        'Connaître le parcours et le legs de Baye Niasse',
                        'Lire et commenter des extraits choisis',
                        'Relier les enseignements à la pratique du centre',
                    ],
                    'en' => [
                        'Know Baye Niasse\'s life and legacy',
                        'Read and comment selected excerpts',
                        'Connect the teachings to centre practice',
                    ],
                    'ar' => [
                        'معرفة مسار باي نياس وإرثه',
                        'قراءة وتعليق مقاطع مختارة',
                        'ربط التعاليم بممارسة المركز',
                    ],
                ],
                'type' => 'baye_niasse',
                'level' => 'avance',
                'duration_weeks' => 36,
                'hours_per_week' => 6,
                'tuition_amount_minor' => 100000,
                'registration_amount_minor' => 15000,
                'min_age' => 16,
                'is_active' => true,
                'is_featured' => false,
                'display_order' => 6,
            ],
            [
                'code' => 'FIQH-HADITH',
                'name_i18n' => [
                    'fr' => 'Sciences islamiques — Fiqh et Hadith',
                    'en' => 'Islamic Sciences — Fiqh and Hadith',
                    'ar' => 'العلوم الإسلامية — الفقه والحديث',
                ],
                'description_i18n' => [
                    'fr' => 'Programme d\'étude du fiqh (jurisprudence) et des hadiths authentiques, avec un focus sur l\'école malikite.',
                    'en' => 'Study of fiqh (jurisprudence) and authentic hadiths, with a focus on the Maliki school.',
                    'ar' => 'برنامج دراسة الفقه والأحاديث الصحيحة مع التركيز على المذهب المالكي.',
                ],
                'objectives_i18n' => [
                    'fr' => [
                        'Comprendre les fondements du fiqh malikite',
                        'Lire des hadiths et situer leur contexte',
                        'Appliquer les enseignements au quotidien',
                    ],
                    'en' => [
                        'Understand foundations of Maliki fiqh',
                        'Read hadiths and place them in context',
                        'Apply teachings in daily life',
                    ],
                    'ar' => [
                        'فهم أسس الفقه المالكي',
                        'قراءة الأحاديث ووضعها في سياقها',
                        'تطبيق التعاليم في الحياة اليومية',
                    ],
                ],
                'type' => 'sunnite',
                'level' => 'intermediaire',
                'duration_weeks' => 44,
                'hours_per_week' => 7,
                'tuition_amount_minor' => 110000,
                'registration_amount_minor' => 18000,
                'min_age' => 14,
                'is_active' => true,
                'is_featured' => true,
                'display_order' => 7,
            ],
            [
                'code' => 'AQIDA-DEB',
                'name_i18n' => [
                    'fr' => 'Fondements de la foi — Aqida',
                    'en' => 'Foundations of Faith — Aqida',
                    'ar' => 'أسس الإيمان — العقيدة',
                ],
                'description_i18n' => [
                    'fr' => 'Introduction claire aux fondements de la croyance islamique : les piliers de la foi, le tawhid et les notions essentielles pour tout musulman.',
                    'en' => 'A clear introduction to Islamic belief: pillars of faith, tawhid and essential notions for every Muslim.',
                    'ar' => 'مقدمة واضحة في أسس العقيدة: أركان الإيمان والتوحيد والمفاهيم الأساسية لكل مسلم.',
                ],
                'objectives_i18n' => [
                    'fr' => [
                        'Connaître les six piliers de la foi',
                        'Comprendre le tawhid et ses implications',
                        'Répondre aux questions courantes avec clarté',
                    ],
                    'en' => [
                        'Know the six pillars of faith',
                        'Understand tawhid and its implications',
                        'Answer common questions with clarity',
                    ],
                    'ar' => [
                        'معرفة أركان الإيمان الستة',
                        'فهم التوحيد وآثاره',
                        'الإجابة عن الأسئلة الشائعة بوضوح',
                    ],
                ],
                'type' => 'sunnite',
                'level' => 'debutant',
                'duration_weeks' => 24,
                'hours_per_week' => 4,
                'tuition_amount_minor' => 70000,
                'registration_amount_minor' => 12000,
                'min_age' => 12,
                'is_active' => true,
                'is_featured' => false,
                'display_order' => 8,
            ],
        ];

        $count = 0;

        foreach ($programs as $programData) {
            $code = $programData['code'];
            unset($programData['code']);

            $payload = array_merge($programData, [
                'organization_id' => $organizationId,
                'currency' => 'XOF',
                'metadata' => ['code' => $code],
            ]);

            $program = Program::withTrashed()
                ->where('organization_id', $organizationId)
                ->where('metadata->code', $code)
                ->first();

            if (! $program) {
                $program = Program::withTrashed()
                    ->where('organization_id', $organizationId)
                    ->where('name_i18n->fr', $programData['name_i18n']['fr'])
                    ->where(function ($query) {
                        $query->whereNull('metadata')
                            ->orWhereNull('metadata->code');
                    })
                    ->first();
            }

            if ($program) {
                if ($program->trashed()) {
                    $program->restore();
                }
                $program->update($payload);
            } else {
                Program::create(array_merge([
                    'id' => (string) Str::ulid(),
                ], $payload));
            }

            $count++;
        }

        // Relier le prérequis Coran débutant → Coran intermédiaire
        $coranDeb = Program::query()
            ->where('organization_id', $organizationId)
            ->where('metadata->code', 'CORAN-DEB')
            ->first();
        $coranInt = Program::query()
            ->where('organization_id', $organizationId)
            ->where('metadata->code', 'CORAN-INT')
            ->first();

        if ($coranDeb && $coranInt) {
            $coranInt->update(['prerequisite_program_id' => $coranDeb->id]);
        }

        $arabeDeb = Program::query()
            ->where('organization_id', $organizationId)
            ->where('metadata->code', 'ARABE-DEB')
            ->first();
        $arabeInt = Program::query()
            ->where('organization_id', $organizationId)
            ->where('metadata->code', 'ARABE-INT')
            ->first();

        if ($arabeDeb && $arabeInt) {
            $arabeInt->update(['prerequisite_program_id' => $arabeDeb->id]);
        }

        $knownCodes = [
            'CORAN-DEB',
            'CORAN-INT',
            'TAJWID-INIT',
            'ARABE-DEB',
            'ARABE-INT',
            'BAYE-OEUVRES',
            'FIQH-HADITH',
            'AQIDA-DEB',
        ];

        foreach ($knownCodes as $code) {
            $duplicates = Program::query()
                ->where('organization_id', $organizationId)
                ->where('metadata->code', $code)
                ->orderBy('created_at')
                ->get();

            $duplicates->skip(1)->each->delete();
        }

        $removed = Program::query()
            ->where('organization_id', $organizationId)
            ->where(function ($query) use ($knownCodes) {
                $query->whereNull('metadata')
                    ->orWhereNull('metadata->code')
                    ->orWhereNotIn('metadata->code', $knownCodes);
            })
            ->delete();

        if ($removed > 0) {
            $this->command?->info("🧹 {$removed} ancien(s) programme(s) retiré(s).");
        }

        $this->command?->info("✅ {$count} programmes synchronisés.");
    }
}
