<?php

declare(strict_types=1);

namespace Modules\Core\Support;

use Illuminate\Support\Facades\Http;
use Modules\Core\Events\AssistantKnowledgeCollecting;
use Throwable;

/**
 * Répond aux questions avec les fiches publiques du site.
 * Une clé OpenAI optionnelle reformule sans sortir de ces fiches.
 */
final class SiteAssistant
{
    private string $lastQuestion = '';

    /**
     * @param  list<array{role: string, text: string}>  $history
     * @return array{reply: string, sources: list<array{title: string, url: ?string}>}
     */
    public function answer(string $question, string $locale, array $history = []): array
    {
        $locale = in_array($locale, ['fr', 'en', 'ar'], true) ? $locale : 'fr';
        $question = trim(mb_substr($question, 0, 500));
        $this->lastQuestion = $question;
        if ($question === '') {
            return [
                'reply' => $this->emptyPrompt($locale),
                'sources' => [],
            ];
        }

        $chunks = $this->knowledge($locale);
        $intent = $this->intent($question);
        if ($intent !== null) {
            $focused = array_values(array_filter(
                $chunks,
                fn (array $chunk): bool => $this->chunkMatchesIntent($chunk['id'], $intent),
            ));
            if ($focused !== []) {
                $chunks = $focused;
            }
        }
        $ranked = $this->rank($question, $chunks, $intent);
        $top = array_slice($ranked, 0, in_array($intent, ['teachers', 'programs'], true) ? 12 : 6);

        if ($top === [] || ($top[0]['score'] ?? 0) < 1.5) {
            return [
                'reply' => $this->unknown($locale),
                'sources' => [],
            ];
        }

        $sources = [];
        foreach ($top as $item) {
            if (($item['chunk']['url'] ?? null) === null) {
                continue;
            }
            $sources[] = [
                'title' => $item['chunk']['title'],
                'url' => $item['chunk']['url'],
            ];
            if (count($sources) >= 3) {
                break;
            }
        }

        $llm = $this->llmReply($question, $locale, $top, $history);
        if ($llm !== null) {
            return [
                'reply' => $llm,
                'sources' => $sources,
            ];
        }

        return [
            'reply' => $this->compose($locale, $top, $intent),
            'sources' => $sources,
        ];
    }

    /**
     * @return list<array{id: string, title: string, body: string, url: ?string}>
     */
    private function knowledge(string $locale): array
    {
        if (! $this->databaseReachable()) {
            return $this->offlineChunks($locale);
        }

        $event = new AssistantKnowledgeCollecting($locale);
        event($event);

        return $event->chunks;
    }

    private function databaseReachable(): bool
    {
        $connection = (string) config('database.default', 'pgsql');
        $host = (string) config("database.connections.{$connection}.host", '127.0.0.1');
        $port = (int) config("database.connections.{$connection}.port", 5432);
        $errno = 0;
        $errstr = '';
        $socket = @stream_socket_client("tcp://{$host}:{$port}", $errno, $errstr, 0.4);
        if (! is_resource($socket)) {
            return false;
        }
        fclose($socket);

        return true;
    }

    /**
     * @return list<array{id: string, title: string, body: string, url: ?string}>
     */
    private function offlineChunks(string $locale): array
    {
        $event = new AssistantKnowledgeCollecting($locale);
        $event->add(
            'contact',
            match ($locale) {
                'en' => 'Contact details',
                'ar' => 'بيانات الاتصال',
                default => 'Coordonnées',
            },
            match ($locale) {
                'en' => "Address: 28M Cité des Magistrats, Sud Foire, Dakar\nPhone: +221 77 123 45 67\nEmail: contact@nujumalhuda.com",
                'ar' => "العنوان: 28M Cité des Magistrats, Sud Foire, Dakar\nالهاتف: +221 77 123 45 67\nالبريد: contact@nujumalhuda.com",
                default => "Adresse : 28M Cité des Magistrats, Sud Foire, Dakar\nTéléphone : +221 77 123 45 67\nE-mail : contact@nujumalhuda.com",
            },
            '/'.$locale.'/centre/contact',
        );
        $event->add(
            'institute',
            'Nujum Al-Huda Institute Center',
            match ($locale) {
                'en' => 'Franco-Anglo-Arabic institute in Dakar. The Quran, the Arabic language, the Islamic sciences and the works of Sheikh Ibrahim Niasse.',
                'ar' => 'معهد فرنسي إنجليزي عربي في دكار. القرآن واللغة العربية والعلوم الإسلامية ومؤلفات الشيخ إبراهيم نياس.',
                default => 'Institut franco-anglo-arabe à Dakar. Le Coran, la langue arabe, les sciences islamiques et les œuvres de Cheikh Ibrahim Niasse.',
            },
            '/'.$locale,
        );

        return $event->chunks;
    }

    /**
     * @param  list<array{id: string, title: string, body: string, url: ?string}>  $chunks
     * @return list<array{score: float, chunk: array{id: string, title: string, body: string, url: ?string}}>
     */
    /**
     * @param  list<array{id: string, title: string, body: string, url: ?string}>  $chunks
     * @return list<array{score: float, chunk: array{id: string, title: string, body: string, url: ?string}}>
     */
    private function rank(string $question, array $chunks, ?string $intent = null): array
    {
        $tokens = $this->tokens($question);
        if ($tokens === [] && $intent === null) {
            return [];
        }

        $scored = [];
        foreach ($chunks as $chunk) {
            $haystack = ' '.$this->words($chunk['title'].' '.$chunk['body']).' ';
            $titleFold = ' '.$this->words($chunk['title']).' ';
            $score = 0.0;
            foreach ($tokens as $token) {
                $needle = ' '.$token.' ';
                if (mb_strpos($haystack, $needle) !== false) {
                    $score += mb_strlen($token) > 4 ? 2.0 : 1.0;
                    if (mb_strpos($titleFold, $needle) !== false) {
                        $score += 1.5;
                    }
                }
            }
            $score += $this->intentBoost($intent, $chunk['id']);
            if ($intent !== null && $this->chunkMatchesIntent($chunk['id'], $intent)) {
                $score += 8.0;
            }
            if ($score <= 0) {
                continue;
            }
            $scored[] = ['score' => $score, 'chunk' => $chunk];
        }

        usort($scored, static fn (array $a, array $b): int => $b['score'] <=> $a['score']);

        return $scored;
    }

    private function intent(string $question): ?string
    {
        $fold = $this->fold($question);
        if (preg_match('/\b(enseignant|enseignants|professeur|professeurs|teacher|teachers|معلم|معلمون|اساتذة)\b/u', $fold) === 1) {
            return 'teachers';
        }
        if (preg_match('/\b(telephone|tel|phone|adresse|address|email|mail|contact|coordonnees|appeler|هاتف|عنوان)\b/u', $fold) === 1) {
            return 'contact';
        }
        if (preg_match('/\b(priere|prieres|prie|pris|prayer|horaire|horaires|heure|fajr|dhuhr|zuhr|asr|maghrib|isha|iqama|subax|sobah|tisbar|tisbaar|tisbaa|takussaan|takusan|timis|gee|صلاة|مواقيت)\b/u', $fold) === 1) {
            return 'prayers';
        }
        if (preg_match('/\b(sinscrire|inscrire|inscription|inscriptions|enroll|enrol|enrollment|dossier|rejoindre|تسجيل|التسجيل)\b/u', $fold) === 1) {
            return 'enroll';
        }
        if (preg_match('/\b(programme|programmes|program|parcours|scolarite|tarif|prix|cours|filiere|برنامج|مسار)\b/u', $fold) === 1) {
            return 'programs';
        }

        return null;
    }

    private function chunkMatchesIntent(string $id, string $intent): bool
    {
        return match ($intent) {
            'teachers' => $id === 'teachers.index' || str_starts_with($id, 'teacher.'),
            'contact' => $id === 'contact' || $id === 'payments',
            'prayers' => str_starts_with($id, 'prayers'),
            'programs' => $id === 'programs.index' || str_starts_with($id, 'program.'),
            'enroll' => $id === 'enroll.index' || str_contains($id, 'enroll'),
            default => false,
        };
    }

    private function intentBoost(?string $intent, string $id): float
    {
        if ($intent === null) {
            return 0.0;
        }

        return $this->chunkMatchesIntent($id, $intent) ? 4.0 : 0.0;
    }

    private function fold(string $text): string
    {
        $text = mb_strtolower($text);
        $map = [
            'à' => 'a', 'á' => 'a', 'â' => 'a', 'ä' => 'a', 'ã' => 'a',
            'ç' => 'c',
            'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e',
            'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i',
            'ñ' => 'n',
            'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'ö' => 'o', 'õ' => 'o',
            'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u',
            'ý' => 'y', 'ÿ' => 'y',
        ];

        return strtr($text, $map);
    }

    private function words(string $text): string
    {
        $normalized = $this->fold($text);
        $normalized = preg_replace('/[^\p{L}\p{N}]+/u', ' ', $normalized) ?? $normalized;

        return trim(preg_replace('/\s+/u', ' ', $normalized) ?? $normalized);
    }

    private function tokens(string $text): array
    {
        $normalized = $this->words($text);
        $normalized = str_replace(
            ['s inscrire', 'sinscrire'],
            ['inscrire', 'inscrire'],
            $normalized,
        );
        $parts = preg_split('/\s+/u', $normalized) ?: [];
        $stop = [
            'le', 'la', 'les', 'un', 'une', 'des', 'de', 'du', 'et', 'ou', 'a', 'à', 'au', 'aux',
            'en', 'dans', 'pour', 'par', 'sur', 'avec', 'est', 'sont', 'je', 'tu', 'il', 'elle',
            'nous', 'vous', 'ils', 'elles', 'mon', 'ma', 'mes', 'ton', 'ta', 'tes', 'son', 'sa', 'ses',
            'notre', 'nos', 'votre', 'vos', 'leur', 'leurs', 'quoi', 'que', 'qui', 'comment',
            'ou', 'où', 'the', 'a', 'an', 'and', 'or', 'of', 'to', 'in', 'on', 'for', 'is', 'are',
            'what', 'where', 'when', 'how', 'do', 'does', 'me', 'my', 'your', 'please',
            'من', 'في', 'على', 'إلى', 'عن', 'ما', 'هل', 'هذا', 'هذه',
        ];
        $aliases = [
            'inscrire' => ['inscription', 'inscriptions', 'enroll'],
            'inscription' => ['inscrire', 'inscriptions', 'enroll'],
            'inscriptions' => ['inscription', 'inscrire'],
            'parcours' => ['programme', 'programmes'],
            'enseignants' => ['enseignant', 'professeur'],
            'enseignant' => ['enseignants', 'professeur'],
            'tisbar' => ['tisbaar', 'dhuhr', 'priere', 'horaire'],
            'tisbaar' => ['tisbar', 'dhuhr', 'priere', 'horaire'],
            'takussaan' => ['asr', 'priere', 'horaire'],
            'takusan' => ['asr', 'takussaan', 'priere'],
            'timis' => ['maghrib', 'priere', 'horaire'],
            'subax' => ['fajr', 'priere', 'horaire'],
            'sobah' => ['fajr', 'subax', 'priere'],
            'gee' => ['isha', 'priere', 'horaire'],
            'pris' => ['priere', 'horaire'],
            'prie' => ['priere', 'horaire'],
            'heure' => ['horaire', 'horaires', 'priere'],
        ];
        $out = [];
        foreach ($parts as $part) {
            $part = trim($part);
            if (mb_strlen($part) < 3 || in_array($part, $stop, true)) {
                continue;
            }
            $out[] = $part;
            foreach ($aliases[$part] ?? [] as $alias) {
                $out[] = $alias;
            }
        }

        return array_values(array_unique($out));
    }

    /**
     * @param  list<array{score: float, chunk: array{id: string, title: string, body: string, url: ?string}}>  $top
     * @param  list<array{role: string, text: string}>  $history
     */
    private function llmReply(string $question, string $locale, array $top, array $history): ?string
    {
        $key = trim((string) config('services.openai.key', ''));
        if ($key === '') {
            return null;
        }

        $facts = [];
        foreach ($top as $item) {
            $chunk = $item['chunk'];
            $facts[] = trim($chunk['title']."\n".$chunk['body']);
        }

        $system = match ($locale) {
            'en' => 'You are Babacar, a helpful human assistant for Nujum Al-Huda Institute. Answer naturally, in the first person, using only the facts below. Never say “according to the site”. If the facts do not contain the answer, say you do not have that information and suggest contacting the centre. Be concise. Do not invent names, prices, times or schedules.',
            'ar' => 'أنت باباكار، مساعد بشري لمعهد نجوم الهدى. أجب بأسلوب طبيعي وبضمير المتكلم اعتماداً فقط على المعلومات أدناه. لا تقل «حسب الموقع». إذا لم تجد الجواب فقل إن المعلومة غير متوفرة واقترح الاتصال بالمركز. كن موجزاً. لا تخترع أسماء أو أسعاراً أو أوقاتاً.',
            default => 'Tu es Babacar, un assistant humain de Nujum Al-Huda Institute. Réponds naturellement, à la première personne, uniquement avec les fiches ci-dessous. Ne dis jamais « d\'après le site ». Si l\'information manque, dis que tu ne l\'as pas et oriente vers le contact du centre. Sois concis. N\'invente aucun nom, tarif, horaire ou date.',
        };

        $messages = [
            ['role' => 'system', 'content' => $system."\n\n".$this->labelFacts($locale)."\n".implode("\n\n---\n\n", $facts)],
        ];
        foreach (array_slice($history, -6) as $turn) {
            $role = ($turn['role'] ?? '') === 'assistant' ? 'assistant' : 'user';
            $text = trim((string) ($turn['text'] ?? ''));
            if ($text === '') {
                continue;
            }
            $messages[] = ['role' => $role, 'content' => mb_substr($text, 0, 800)];
        }
        $messages[] = ['role' => 'user', 'content' => $question];

        try {
            $endpoint = rtrim((string) config('services.openai.base_url', 'https://api.openai.com/v1'), '/').'/chat/completions';
            $response = Http::withToken($key)
                ->timeout(25)
                ->post($endpoint, [
                    'model' => (string) config('services.openai.model', 'gpt-4o-mini'),
                    'temperature' => 0.2,
                    'messages' => $messages,
                ]);
            if (! $response->successful()) {
                return null;
            }
            $text = trim((string) data_get($response->json(), 'choices.0.message.content', ''));

            return $text !== '' ? $text : null;
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * @param  list<array{score: float, chunk: array{id: string, title: string, body: string, url: ?string}}>  $top
     */
    /**
     * @param  list<array{score: float, chunk: array{id: string, title: string, body: string, url: ?string}}>  $top
     */
    private function compose(string $locale, array $top, ?string $intent = null): string
    {
        foreach ($top as $item) {
            $id = $item['chunk']['id'] ?? '';
            if ($id === 'teachers.index') {
                $intro = match ($locale) {
                    'en' => 'Here are our teachers:',
                    'ar' => 'إليك معلمينا:',
                    default => 'Voici nos enseignants :',
                };

                return $intro."\n".$item['chunk']['body'];
            }
            if ($id === 'programs.index') {
                $intro = match ($locale) {
                    'en' => 'Here are our programmes:',
                    'ar' => 'إليك برامجنا:',
                    default => 'Voici nos parcours :',
                };

                return $intro."\n".$item['chunk']['body'];
            }
            if ($intent === 'contact' && $id === 'contact') {
                $intro = match ($locale) {
                    'en' => 'You can reach us here:',
                    'ar' => 'يمكنك التواصل معنا هنا:',
                    default => 'Vous pouvez nous joindre ici :',
                };

                return $intro."\n".trim(html_entity_decode(strip_tags($item['chunk']['body']), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            }
            if ($intent === 'enroll' && $id === 'enroll.index') {
                return trim(html_entity_decode(strip_tags($item['chunk']['body']), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            }
            if ($intent === 'prayers' && str_starts_with($id, 'prayers')) {
                $body = trim(html_entity_decode(strip_tags($item['chunk']['body']), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
                $focused = $this->focusPrayer($locale, $body);
                if ($focused !== null) {
                    return $focused;
                }
                $intro = match ($locale) {
                    'en' => 'Here are today’s prayer times:',
                    'ar' => 'إليك أوقات الصلاة اليوم:',
                    default => 'Voici les horaires de prière du jour :',
                };

                return $intro."\n".$body;
            }
        }

        $parts = [];
        foreach (array_slice($top, 0, 3) as $item) {
            $chunk = $item['chunk'];
            $body = trim(html_entity_decode(strip_tags($chunk['body']), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            $parts[] = $body !== ''
                ? $chunk['title']."\n".$body
                : $chunk['title'];
        }

        return implode("\n\n", $parts);
    }

    private function focusPrayer(string $locale, string $body): ?string
    {
        // Rempli dans answer() via la question courante.
        $question = $this->fold($this->lastQuestion ?? '');
        $map = [
            'fajr' => ['fajr', 'subax', 'sobah'],
            'dhuhr' => ['dhuhr', 'zuhr', 'tisbar', 'tisbaar', 'tisbaa'],
            'asr' => ['asr', 'takussaan', 'takusan'],
            'maghrib' => ['maghrib', 'timis'],
            'isha' => ['isha', 'gee'],
        ];
        $wanted = null;
        foreach ($map as $key => $aliases) {
            foreach ($aliases as $alias) {
                if (preg_match('/\b'.preg_quote($alias, '/').'\b/u', $question) === 1) {
                    $wanted = $key;
                    break 2;
                }
            }
        }
        if ($wanted === null) {
            return null;
        }

        foreach (preg_split("/\n+/", $body) ?: [] as $line) {
            $fold = $this->fold($line);
            foreach ($map[$wanted] as $alias) {
                if (str_contains($fold, $alias)) {
                    $label = match ($wanted) {
                        'fajr' => match ($locale) {
                            'en' => 'Fajr (Subax)',
                            'ar' => 'الفجر (صباح)',
                            default => 'Fajr (Subax)',
                        },
                        'dhuhr' => match ($locale) {
                            'en' => 'Dhuhr (Tisbaar)',
                            'ar' => 'الظهر (تيسبار)',
                            default => 'Dhuhr (Tisbaar)',
                        },
                        'asr' => match ($locale) {
                            'en' => 'Asr (Takussaan)',
                            'ar' => 'العصر (تاكوسان)',
                            default => 'Asr (Takussaan)',
                        },
                        'maghrib' => match ($locale) {
                            'en' => 'Maghrib (Timis)',
                            'ar' => 'المغرب (تيميس)',
                            default => 'Maghrib (Timis)',
                        },
                        'isha' => match ($locale) {
                            'en' => 'Isha (Gee)',
                            'ar' => 'العشاء (كيي)',
                            default => 'Isha (Gee)',
                        },
                        default => $wanted,
                    };
                    if (preg_match('/(\d{1,2}:\d{2}).*?(\d{1,2}:\d{2})/', $line, $times) === 1) {
                        return match ($locale) {
                            'en' => "{$label} is at {$times[1]}, iqama at {$times[2]}.",
                            'ar' => "{$label} في الساعة {$times[1]}، والإقامة الساعة {$times[2]}.",
                            default => "{$label} est à {$times[1]}, iqama à {$times[2]}.",
                        };
                    }
                    if (preg_match('/(\d{1,2}:\d{2})/', $line, $times) === 1) {
                        return match ($locale) {
                            'en' => "{$label} is at {$times[1]}.",
                            'ar' => "{$label} في الساعة {$times[1]}.",
                            default => "{$label} est à {$times[1]}.",
                        };
                    }
                }
            }
        }

        return null;
    }

    private function unknown(string $locale): string
    {
        return match ($locale) {
            'en' => 'I don’t have that information yet. You can write to contact@nujumalhuda.com or call the centre.',
            'ar' => 'ليست لدي هذه المعلومة بعد. يمكنك مراسلة contact@nujumalhuda.com أو الاتصال بالمركز.',
            default => 'Je n’ai pas encore cette information. Vous pouvez écrire à contact@nujumalhuda.com ou appeler le centre.',
        };
    }

    private function emptyPrompt(string $locale): string
    {
        return match ($locale) {
            'en' => 'Ask a question about the centre, programmes, prayer times or contact details.',
            'ar' => 'اسأل عن المركز أو البرامج أو أوقات الصلاة أو بيانات الاتصال.',
            default => 'Posez une question sur le centre, les programmes, les horaires de prière ou les coordonnées.',
        };
    }

    private function labelFacts(string $locale): string
    {
        return match ($locale) {
            'en' => 'Facts you may use:',
            'ar' => 'معلومات يمكنك استخدامها:',
            default => 'Informations disponibles :',
        };
    }
}
