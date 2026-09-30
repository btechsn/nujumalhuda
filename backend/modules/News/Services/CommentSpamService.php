<?php

namespace Modules\News\Services;

use Illuminate\Support\Facades\Cache;
use Modules\Core\Models\User;
use Modules\News\Models\ArticleComment;

class CommentSpamService
{
    /**
     * Seuil à partir duquel le commentaire est classé spam.
     */
    public const SPAM_THRESHOLD = 60;

    /**
     * Seuil à partir duquel le commentaire est seulement signalé.
     */
    public const FLAG_THRESHOLD = 35;

    /**
     * @return array{score: int, flags: array<int, string>, status: string}
     */
    public function analyze(User $user, string $content, ?string $honeypot, string $ip): array
    {
        $flags = [];
        $score = 0;
        $normalized = mb_strtolower(trim($content));

        if (filled($honeypot)) {
            $flags[] = 'honeypot';
            $score += 100;
        }

        $linkCount = preg_match_all('/https?:\/\/|www\./i', $content);
        if ($linkCount >= 3) {
            $flags[] = 'too_many_links';
            $score += 40;
        } elseif ($linkCount >= 1) {
            $flags[] = 'contains_link';
            $score += 15;
        }

        foreach ($this->bannedPhrases() as $phrase) {
            if (str_contains($normalized, $phrase)) {
                $flags[] = 'banned_phrase';
                $score += 45;
                break;
            }
        }

        if (preg_match('/(.)\1{7,}/u', $content)) {
            $flags[] = 'repeated_characters';
            $score += 25;
        }

        $letters = preg_replace('/[^A-Za-zÀ-ÿ]/u', '', $content) ?? '';
        if (mb_strlen($letters) >= 20 && mb_strtoupper($letters) === $letters) {
            $flags[] = 'all_caps';
            $score += 20;
        }

        $recentDuplicate = ArticleComment::query()
            ->where('user_id', $user->id)
            ->where('content', $content)
            ->where('created_at', '>=', now()->subDay())
            ->exists();

        if ($recentDuplicate) {
            $flags[] = 'duplicate';
            $score += 50;
        }

        $rateKey = 'news.comments.rate.' . $user->id;
        $attempts = (int) Cache::get($rateKey, 0);
        if ($attempts >= 5) {
            $flags[] = 'rate_limit';
            $score += 40;
        }

        $ipKey = 'news.comments.ip.' . sha1($ip);
        $ipAttempts = (int) Cache::get($ipKey, 0);
        if ($ipAttempts >= 12) {
            $flags[] = 'ip_rate_limit';
            $score += 30;
        }

        $status = 'pending';
        if ($score >= self::SPAM_THRESHOLD) {
            $status = 'spam';
        } elseif ($score >= self::FLAG_THRESHOLD) {
            $status = 'flagged';
        }

        return [
            'score' => min($score, 100),
            'flags' => array_values(array_unique($flags)),
            'status' => $status,
        ];
    }

    public function rememberAttempt(User $user, string $ip): void
    {
        $rateKey = 'news.comments.rate.' . $user->id;
        $ipKey = 'news.comments.ip.' . sha1($ip);

        Cache::put($rateKey, ((int) Cache::get($rateKey, 0)) + 1, now()->addHour());
        Cache::put($ipKey, ((int) Cache::get($ipKey, 0)) + 1, now()->addHour());
    }

    /**
     * @return array<int, string>
     */
    private function bannedPhrases(): array
    {
        return [
            'viagra',
            'casino',
            'crypto airdrop',
            'buy followers',
            'work from home',
            'cliquez ici pour gagner',
            'argent facile',
            'prêt rapide',
        ];
    }
}
