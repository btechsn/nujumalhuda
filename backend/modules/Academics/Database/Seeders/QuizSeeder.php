<?php

namespace Modules\Academics\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Academics\Models\Quiz;

class QuizSeeder extends Seeder
{
    public function run(): void
    {
        $quizzes = [
            [
                'slug' => 'tajwid-madd',
                'topic' => 'tajwid',
                'title_i18n' => [
                    'fr' => 'Le madd naturel',
                    'en' => 'Natural madd',
                    'ar' => 'المد الطبيعي',
                ],
                'questions' => [
                    [
                        'prompt_i18n' => [
                            'fr' => 'Combien de harakât dure le madd tabîʿî ?',
                            'en' => 'How many harakat is the natural madd?',
                            'ar' => 'كم حركة في المد الطبيعي؟',
                        ],
                        'choices' => ['1', '2', '4', '6'],
                        'correct_index' => 1,
                    ],
                    [
                        'prompt_i18n' => [
                            'fr' => 'Le madd tabîʿî survient quand une lettre de madd est suivie de…',
                            'en' => 'Natural madd occurs when a madd letter is followed by…',
                            'ar' => 'يأتي المد الطبيعي إذا جاء بعد حرف المد…',
                        ],
                        'choices' => [
                            'Une hamza',
                            'Rien (sukûn ni hamza)',
                            'Un shadda',
                            'Un tanwîn',
                        ],
                        'correct_index' => 1,
                    ],
                    [
                        'prompt_i18n' => [
                            'fr' => 'Les lettres du madd sont…',
                            'en' => 'The letters of madd are…',
                            'ar' => 'حروف المد هي…',
                        ],
                        'choices' => ['ا و ي', 'ب ت ث', 'ق ك', 'ل م ن'],
                        'correct_index' => 0,
                    ],
                ],
            ],
            [
                'slug' => 'tajwid-idgham',
                'topic' => 'tajwid',
                'title_i18n' => [
                    'fr' => 'L\'idghâm',
                    'en' => 'Idgham',
                    'ar' => 'الإدغام',
                ],
                'questions' => [
                    [
                        'prompt_i18n' => [
                            'fr' => 'L\'idghâm concerne surtout…',
                            'en' => 'Idgham mainly concerns…',
                            'ar' => 'الإدغام يتعلق خاصة بـ…',
                        ],
                        'choices' => [
                            'Le nûn sâkin et le tanwîn',
                            'Le madd seulement',
                            'Les voyelles longues',
                            'Le waqf',
                        ],
                        'correct_index' => 0,
                    ],
                    [
                        'prompt_i18n' => [
                            'fr' => 'Combien de lettres d\'idghâm avec ghunna ?',
                            'en' => 'How many idgham letters have ghunna?',
                            'ar' => 'كم حرفاً للإدغام بغنة؟',
                        ],
                        'choices' => ['2', '4', '6', '8'],
                        'correct_index' => 1,
                    ],
                    [
                        'prompt_i18n' => [
                            'fr' => 'Les lettres d\'idghâm avec ghunna sont regroupées dans…',
                            'en' => 'Idgham-with-ghunna letters are gathered in…',
                            'ar' => 'حروف الإدغام بغنة مجموعة في…',
                        ],
                        'choices' => ['ينمو', 'قطب جد', 'فر من لب', 'خص ضغط قظ'],
                        'correct_index' => 0,
                    ],
                ],
            ],
            [
                'slug' => 'coran-juz-amma',
                'topic' => 'coran',
                'title_i18n' => [
                    'fr' => 'Juz Amma — bases',
                    'en' => 'Juz Amma — basics',
                    'ar' => 'جزء عم — أساسيات',
                ],
                'questions' => [
                    [
                        'prompt_i18n' => [
                            'fr' => 'Juz Amma correspond au juz numéro…',
                            'en' => 'Juz Amma is juz number…',
                            'ar' => 'جزء عم هو الجزء رقم…',
                        ],
                        'choices' => ['1', '15', '28', '30'],
                        'correct_index' => 3,
                    ],
                    [
                        'prompt_i18n' => [
                            'fr' => 'La sourate An-Nâs est…',
                            'en' => 'Surah An-Nas is…',
                            'ar' => 'سورة الناس هي…',
                        ],
                        'choices' => [
                            'La première du Coran',
                            'La dernière du Coran',
                            'Au milieu du Coran',
                            'Dans le Juz 1',
                        ],
                        'correct_index' => 1,
                    ],
                    [
                        'prompt_i18n' => [
                            'fr' => 'Combien de sourates comporte le Juz Amma (approximativement) ?',
                            'en' => 'About how many surahs are in Juz Amma?',
                            'ar' => 'كم تقريباً عدد سور جزء عم؟',
                        ],
                        'choices' => ['7', '15', '37', '60'],
                        'correct_index' => 2,
                    ],
                ],
            ],
            [
                'slug' => 'arabe-alphabet',
                'topic' => 'arabe',
                'title_i18n' => [
                    'fr' => 'Alphabet arabe',
                    'en' => 'Arabic alphabet',
                    'ar' => 'الحروف العربية',
                ],
                'questions' => [
                    [
                        'prompt_i18n' => [
                            'fr' => 'Combien de lettres compte l\'alphabet arabe ?',
                            'en' => 'How many letters are in the Arabic alphabet?',
                            'ar' => 'كم حرفاً في الأبجدية العربية؟',
                        ],
                        'choices' => ['22', '26', '28', '32'],
                        'correct_index' => 2,
                    ],
                    [
                        'prompt_i18n' => [
                            'fr' => 'L\'arabe se lit…',
                            'en' => 'Arabic is read…',
                            'ar' => 'تُقرأ العربية من…',
                        ],
                        'choices' => [
                            'De gauche à droite',
                            'De droite à gauche',
                            'De haut en bas seulement',
                            'Dans les deux sens',
                        ],
                        'correct_index' => 1,
                    ],
                    [
                        'prompt_i18n' => [
                            'fr' => 'La lettre « ب » se prononce…',
                            'en' => 'The letter « ب » is pronounced…',
                            'ar' => 'حرف « ب » يُنطق…',
                        ],
                        'choices' => ['ba', 'ta', 'tha', 'jim'],
                        'correct_index' => 0,
                    ],
                    [
                        'prompt_i18n' => [
                            'fr' => 'Les voyelles brèves en arabe s\'appellent…',
                            'en' => 'Short vowels in Arabic are called…',
                            'ar' => 'الحركات القصيرة تُسمى…',
                        ],
                        'choices' => ['Harakât', 'Madd', 'Sukûn', 'Shadda'],
                        'correct_index' => 0,
                    ],
                ],
            ],
            [
                'slug' => 'arabe-nahw-bases',
                'topic' => 'arabe',
                'title_i18n' => [
                    'fr' => 'Nahw — bases',
                    'en' => 'Nahw — basics',
                    'ar' => 'النحو — أساسيات',
                ],
                'questions' => [
                    [
                        'prompt_i18n' => [
                            'fr' => 'Le nahw étudie principalement…',
                            'en' => 'Nahw mainly studies…',
                            'ar' => 'النحو يدرس أساساً…',
                        ],
                        'choices' => [
                            'La grammaire de la phrase',
                            'La récitation seulement',
                            'La calligraphie',
                            'Les dates hégiriennes',
                        ],
                        'correct_index' => 0,
                    ],
                    [
                        'prompt_i18n' => [
                            'fr' => 'Un ism est…',
                            'en' => 'An ism is…',
                            'ar' => 'الاسم هو…',
                        ],
                        'choices' => [
                            'Un verbe',
                            'Un nom',
                            'Une préposition seulement',
                            'Un chiffre',
                        ],
                        'correct_index' => 1,
                    ],
                    [
                        'prompt_i18n' => [
                            'fr' => 'Le fiʿl désigne…',
                            'en' => 'The fiʿl refers to…',
                            'ar' => 'الفعل يدل على…',
                        ],
                        'choices' => [
                            'Un lieu',
                            'Un verbe',
                            'Un adjectif seulement',
                            'Un pronom',
                        ],
                        'correct_index' => 1,
                    ],
                ],
            ],
            [
                'slug' => 'fiqh-wudu',
                'topic' => 'fiqh',
                'title_i18n' => [
                    'fr' => 'Les ablutions (wudûʾ)',
                    'en' => 'Ablutions (wudu)',
                    'ar' => 'الوضوء',
                ],
                'questions' => [
                    [
                        'prompt_i18n' => [
                            'fr' => 'Le wudûʾ est obligatoire avant…',
                            'en' => 'Wudu is required before…',
                            'ar' => 'الوضوء واجب قبل…',
                        ],
                        'choices' => [
                            'La salât',
                            'Le sommeil seulement',
                            'Le repas',
                            'La lecture d\'un livre',
                        ],
                        'correct_index' => 0,
                    ],
                    [
                        'prompt_i18n' => [
                            'fr' => 'Parmi les piliers du wudûʾ (selon beaucoup de savants)…',
                            'en' => 'Among the pillars of wudu (according to many scholars)…',
                            'ar' => 'من أركان الوضوء عند كثير من العلماء…',
                        ],
                        'choices' => [
                            'Laver le visage',
                            'Porter un chapeau',
                            'Manger du sel',
                            'S\'asseoir',
                        ],
                        'correct_index' => 0,
                    ],
                    [
                        'prompt_i18n' => [
                            'fr' => 'L\'école dominante au Sénégal pour le fiqh est…',
                            'en' => 'The dominant fiqh school in Senegal is…',
                            'ar' => 'المذهب الفقهي الغالب في السنغال هو…',
                        ],
                        'choices' => ['Malikite', 'Hanafite', 'Shafiite', 'Hanbalite'],
                        'correct_index' => 0,
                    ],
                ],
            ],
            [
                'slug' => 'baye-niasse-intro',
                'topic' => 'baye_niasse',
                'title_i18n' => [
                    'fr' => 'Baye Niasse — introduction',
                    'en' => 'Baye Niasse — introduction',
                    'ar' => 'باي نياس — مقدمة',
                ],
                'questions' => [
                    [
                        'prompt_i18n' => [
                            'fr' => 'Cheikh Ibrahim Niasse est aussi appelé…',
                            'en' => 'Shaykh Ibrahim Niasse is also called…',
                            'ar' => 'الشيخ إبراهيم نياس يُعرف أيضاً بـ…',
                        ],
                        'choices' => ['Baye Niasse', 'Al-Bukhari', 'Ibn Sina', 'Al-Ghazali'],
                        'correct_index' => 0,
                    ],
                    [
                        'prompt_i18n' => [
                            'fr' => 'Il est une figure majeure associée à…',
                            'en' => 'He is a major figure associated with…',
                            'ar' => 'هو شخصية بارزة مرتبطة بـ…',
                        ],
                        'choices' => [
                            'La Tidjaniyya',
                            'La médecine grecque',
                            'Le droit romain',
                            'La poésie préislamique seulement',
                        ],
                        'correct_index' => 0,
                    ],
                    [
                        'prompt_i18n' => [
                            'fr' => 'Kaolack est liée à son héritage notamment comme…',
                            'en' => 'Kaolack is linked to his legacy especially as…',
                            'ar' => 'كاولاخ مرتبطة بإرثه خصوصاً بوصفها…',
                        ],
                        'choices' => [
                            'Un lieu de rayonnement tidjane',
                            'Une capitale européenne',
                            'Un désert sans habitants',
                            'Un port antique romain',
                        ],
                        'correct_index' => 0,
                    ],
                ],
            ],
            [
                'slug' => 'aqida-pillars',
                'topic' => 'fiqh',
                'title_i18n' => [
                    'fr' => 'Les piliers de la foi',
                    'en' => 'Pillars of faith',
                    'ar' => 'أركان الإيمان',
                ],
                'questions' => [
                    [
                        'prompt_i18n' => [
                            'fr' => 'Combien y a-t-il de piliers de la foi (îmân) ?',
                            'en' => 'How many pillars of faith (iman) are there?',
                            'ar' => 'كم عدد أركان الإيمان؟',
                        ],
                        'choices' => ['3', '5', '6', '7'],
                        'correct_index' => 2,
                    ],
                    [
                        'prompt_i18n' => [
                            'fr' => 'Croire aux anges fait partie…',
                            'en' => 'Belief in the angels is part of…',
                            'ar' => 'الإيمان بالملائكة من…',
                        ],
                        'choices' => [
                            'Des piliers de l\'îmân',
                            'Des règles du commerce',
                            'Des règles de grammaire',
                            'Des règles de calligraphie',
                        ],
                        'correct_index' => 0,
                    ],
                    [
                        'prompt_i18n' => [
                            'fr' => 'Les piliers de l\'islam sont au nombre de…',
                            'en' => 'The pillars of Islam number…',
                            'ar' => 'أركان الإسلام عددها…',
                        ],
                        'choices' => ['3', '4', '5', '6'],
                        'correct_index' => 2,
                    ],
                ],
            ],
        ];

        $count = 0;

        foreach ($quizzes as $quiz) {
            Quiz::updateOrCreate(
                ['slug' => $quiz['slug']],
                [
                    'topic' => $quiz['topic'],
                    'title_i18n' => $quiz['title_i18n'],
                    'questions' => $quiz['questions'],
                    'is_published' => true,
                ],
            );
            $count++;
        }

        $this->command?->info("✅ {$count} quiz synchronisés.");
    }
}
