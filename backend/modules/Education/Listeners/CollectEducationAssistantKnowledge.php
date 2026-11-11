<?php

declare(strict_types=1);

namespace Modules\Education\Listeners;

use Modules\Core\Events\AssistantKnowledgeCollecting;
use Modules\Core\Support\EditorialText;
use Modules\Education\Models\Program;
use Modules\Education\Models\Teacher;
use Throwable;

class CollectEducationAssistantKnowledge
{
    public function handle(AssistantKnowledgeCollecting $event): void
    {
        try {
            $this->collect($event);
        } catch (Throwable) {
            // Une base indisponible n'empêche pas les autres fiches.
        }
    }

    private function collect(AssistantKnowledgeCollecting $event): void
    {
        $programs = Program::query()
            ->where('is_active', true)
            ->orderBy('display_order')
            ->limit(40)
            ->get();

        $programNames = [];
        foreach ($programs as $program) {
            try {
                $name = $this->textField($program->name_i18n, $event->locale);
                $description = $this->textField($program->description_i18n, $event->locale);
                $objectives = $this->textField($program->objectives_i18n, $event->locale);
                if ($name === '') {
                    continue;
                }
                $programNames[] = $name;

                $lines = array_filter([
                    $description,
                    $objectives,
                    $this->ages($program, $event->locale),
                    $this->duration($program, $event->locale),
                    $this->fees($program, $event->locale),
                ]);

                $event->add(
                    'program.'.$program->id,
                    $name,
                    implode("\n", $lines),
                    '/'.$event->locale.'/academique/programmes/'.$program->id,
                );
            } catch (Throwable) {
                continue;
            }
        }

        if ($programNames !== []) {
            $event->add(
                'programs.index',
                match ($event->locale) {
                    'en' => 'Programmes of the institute',
                    'ar' => 'برامج المعهد',
                    default => 'Parcours de l’institut',
                },
                implode("\n", array_map(static fn (string $name): string => '• '.$name, $programNames)),
                '/'.$event->locale.'/academique/programmes',
            );
        }

        $teachers = Teacher::query()
            ->with('user')
            ->available()
            ->ordered()
            ->limit(30)
            ->get();

        $names = [];
        foreach ($teachers as $teacher) {
            try {
                $first = trim((string) ($teacher->user?->first_name ?? ''));
                $last = trim((string) ($teacher->user?->last_name ?? ''));
                $name = trim($first.' '.$last);
                if ($name === '') {
                    continue;
                }
                $names[] = $name;
                $bio = $this->textField($teacher->bio_i18n, $event->locale);
                $specialties = $this->textField($teacher->specialties_i18n, $event->locale);
                $event->add(
                    'teacher.'.$teacher->id,
                    $name,
                    implode("\n", array_filter([$specialties, $bio])),
                    '/'.$event->locale.'/academique/enseignants/'.$teacher->id,
                );
            } catch (Throwable) {
                continue;
            }
        }

        if ($names !== []) {
            $event->add(
                'teachers.index',
                match ($event->locale) {
                    'en' => 'Teachers of the institute',
                    'ar' => 'معلمو المعهد',
                    default => 'Enseignants de l’institut',
                },
                implode("\n", array_map(static fn (string $name): string => '• '.$name, $names)),
                '/'.$event->locale.'/academique/enseignants',
            );
        }
    }

    /**
     * @param  array<string, mixed>|null  $map
     */
    private function textField(?array $map, string $locale): string
    {
        if ($map === null || $map === []) {
            return '';
        }

        $requested = in_array($locale, ['en', 'ar'], true) ? $locale : 'fr';
        $value = $map[$requested] ?? null;
        if ($value === null || $value === '' || (is_array($value) && $value === [])) {
            $value = $map['fr'] ?? null;
        }
        if (is_array($value)) {
            if (array_is_list($value)) {
                return implode(', ', array_values(array_filter(array_map(
                    static fn (mixed $item): string => is_scalar($item) ? trim((string) $item) : '',
                    $value,
                ))));
            }

            return EditorialText::present($value, $locale)['text'];
        }

        if (! is_scalar($value)) {
            return '';
        }

        $text = trim(html_entity_decode(strip_tags((string) $value), ENT_QUOTES | ENT_HTML5, 'UTF-8'));

        return preg_replace('/\s+/u', ' ', $text) ?? $text;
    }

    private function ages(Program $program, string $locale): ?string
    {
        if ($program->min_age === null && $program->max_age === null) {
            return null;
        }
        $min = $program->min_age;
        $max = $program->max_age;
        if ($min !== null && $max !== null) {
            return match ($locale) {
                'en' => "Ages {$min} to {$max}.",
                'ar' => "الأعمار من {$min} إلى {$max}.",
                default => "Âges de {$min} à {$max} ans.",
            };
        }
        if ($min !== null) {
            return match ($locale) {
                'en' => "From age {$min}.",
                'ar' => "من سن {$min}.",
                default => "À partir de {$min} ans.",
            };
        }

        return match ($locale) {
            'en' => "Up to age {$max}.",
            'ar' => "حتى سن {$max}.",
            default => "Jusqu'à {$max} ans.",
        };
    }

    private function duration(Program $program, string $locale): ?string
    {
        $parts = [];
        if ($program->duration_weeks) {
            $parts[] = match ($locale) {
                'en' => $program->duration_weeks.' weeks',
                'ar' => $program->duration_weeks.' أسابيع',
                default => $program->duration_weeks.' semaines',
            };
        }
        if ($program->hours_per_week) {
            $parts[] = match ($locale) {
                'en' => $program->hours_per_week.' hours per week',
                'ar' => $program->hours_per_week.' ساعات في الأسبوع',
                default => $program->hours_per_week.' heures par semaine',
            };
        }

        return $parts === [] ? null : implode(', ', $parts).'.';
    }

    private function fees(Program $program, string $locale): ?string
    {
        $currency = strtoupper((string) ($program->currency ?: 'XOF'));
        $lines = [];
        if ($program->registration_amount_minor) {
            $amount = number_format($program->registration_amount_minor / 100, 0, ',', ' ');
            $lines[] = match ($locale) {
                'en' => "Registration fee: {$amount} {$currency}.",
                'ar' => "رسوم التسجيل: {$amount} {$currency}.",
                default => "Frais d'inscription : {$amount} {$currency}.",
            };
        }
        if ($program->tuition_amount_minor) {
            $amount = number_format($program->tuition_amount_minor / 100, 0, ',', ' ');
            $lines[] = match ($locale) {
                'en' => "Tuition: {$amount} {$currency}.",
                'ar' => "الرسوم الدراسية: {$amount} {$currency}.",
                default => "Scolarité : {$amount} {$currency}.",
            };
        }

        return $lines === [] ? null : implode(' ', $lines);
    }
}
