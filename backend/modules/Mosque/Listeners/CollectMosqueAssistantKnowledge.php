<?php

declare(strict_types=1);

namespace Modules\Mosque\Listeners;

use Modules\Core\Events\AssistantKnowledgeCollecting;
use Modules\Core\Models\Organization;
use Modules\Core\Support\EditorialText;
use Modules\Mosque\Models\Khutba;
use Modules\Mosque\Services\PrayerTimeService;
use Throwable;

class CollectMosqueAssistantKnowledge
{
    public function __construct(
        private PrayerTimeService $prayerTimes,
    ) {
    }

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
        $organizationId = Organization::query()->where('slug', 'nujum-al-huda')->value('id');
        if (is_string($organizationId) && $organizationId !== '') {
            try {
                $date = now('Africa/Dakar')->toDateString();
                $prayers = $this->prayerTimes->getPrayerTimesForDate($date, $organizationId);
                $lines = [];
                foreach (['fajr', 'dhuhr', 'asr', 'maghrib', 'isha'] as $name) {
                    $row = $prayers[$name] ?? null;
                    if (! is_array($row)) {
                        continue;
                    }
                    $adhan = $this->clock($row['adhan'] ?? null);
                    $iqama = $this->clock($row['iqama'] ?? null);
                    if ($adhan === null) {
                        continue;
                    }
                    $label = $this->prayerName($name, $event->locale);
                    $alias = $this->prayerAlias($name);
                    $lines[] = $iqama
                        ? "{$label} ({$alias}) : {$adhan} (iqama {$iqama})"
                        : "{$label} ({$alias}) : {$adhan}";
                }
                if ($lines !== []) {
                    $body = implode("\n", $lines);
                    $event->add(
                        'prayers.today',
                        match ($event->locale) {
                            'en' => 'Prayer times for '.$date,
                            'ar' => 'أوقات الصلاة ليوم '.$date,
                            default => 'Horaires de prière du '.$date,
                        },
                        $body."\n".'Noms locaux : subax, tisbaar, tisbar, takussaan, timis, gee.',
                        '/'.$event->locale.'/centre/horaires',
                    );
                }
            } catch (Throwable) {
                // L'assistant continue sans les horaires si le calcul échoue.
            }
        }

        $khutbas = Khutba::query()
            ->where('is_published', true)
            ->orderByDesc('date')
            ->limit(5)
            ->get();

        foreach ($khutbas as $khutba) {
            $title = EditorialText::present($khutba->title_i18n, $event->locale)['text'];
            if ($title === '') {
                continue;
            }
            $summary = EditorialText::present($khutba->summary_i18n, $event->locale)['text'];
            $event->add(
                'khutba.'.$khutba->id,
                $title,
                implode("\n", array_filter([
                    $khutba->date?->toDateString(),
                    $summary,
                ])),
                '/'.$event->locale.'/centre/khutbas/'.$khutba->id,
            );
        }
    }

    private function clock(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        if ($value instanceof \DateTimeInterface) {
            return $value->format('H:i');
        }
        $text = (string) $value;
        if (preg_match('/(\d{1,2}:\d{2})/', $text, $matches) === 1) {
            return $matches[1];
        }

        return $text;
    }

    private function prayerName(string $name, string $locale): string
    {
        return match ($name) {
            'fajr' => match ($locale) {
                'en' => 'Fajr',
                'ar' => 'الفجر',
                default => 'Fajr',
            },
            'dhuhr' => match ($locale) {
                'en' => 'Dhuhr',
                'ar' => 'الظهر',
                default => 'Dhuhr',
            },
            'asr' => match ($locale) {
                'en' => 'Asr',
                'ar' => 'العصر',
                default => 'Asr',
            },
            'maghrib' => match ($locale) {
                'en' => 'Maghrib',
                'ar' => 'المغرب',
                default => 'Maghrib',
            },
            'isha' => match ($locale) {
                'en' => 'Isha',
                'ar' => 'العشاء',
                default => 'Isha',
            },
            default => $name,
        };
    }

    private function prayerAlias(string $name): string
    {
        return match ($name) {
            'fajr' => 'subax',
            'dhuhr' => 'tisbaar / tisbar',
            'asr' => 'takussaan',
            'maghrib' => 'timis',
            'isha' => 'gee',
            default => $name,
        };
    }
}
