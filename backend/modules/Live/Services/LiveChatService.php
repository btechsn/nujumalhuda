<?php

namespace Modules\Live\Services;

use Modules\Live\Models\LiveChatMessage;
use Modules\Live\Models\LiveSession;
use Modules\Live\Models\LiveStream;
use Illuminate\Support\Collection;

class LiveChatService
{
    /**
     * Analyze message for spam content
     */
    public function analyzeSpam(string $message): array
    {
        $spamScore = 0;
        $flags = [];

        // Check for excessive uppercase
        $uppercaseRatio = $this->getUppercaseRatio($message);
        if ($uppercaseRatio > 0.7) {
            $spamScore += 25;
            $flags[] = 'excessive_uppercase';
        }

        // Check for excessive links
        $linkCount = substr_count(strtolower($message), 'http');
        if ($linkCount > 2) {
            $spamScore += 30;
            $flags[] = 'excessive_links';
        }

        // Check for repeated characters
        if (preg_match('/(.)\1{4,}/', $message)) {
            $spamScore += 15;
            $flags[] = 'repeated_characters';
        }

        // Check for banned words (Islamic context appropriate)
        $bannedWords = config('live.chat.banned_words', [
            'spam', 'casino', 'viagra', 'porn',
        ]);

        foreach ($bannedWords as $word) {
            if (stripos($message, $word) !== false) {
                $spamScore += 50;
                $flags[] = 'banned_words';
                break;
            }
        }

        // Check message length
        if (strlen($message) > 500) {
            $spamScore += 10;
            $flags[] = 'excessive_length';
        }

        return [
            'score' => min($spamScore, 100),
            'flags' => $flags,
            'is_spam' => $spamScore >= 70,
        ];
    }

    /**
     * Calculate uppercase ratio in message
     */
    protected function getUppercaseRatio(string $message): float
    {
        $letters = preg_replace('/[^a-zA-Z]/', '', $message);
        if (strlen($letters) === 0) {
            return 0;
        }

        $uppercase = preg_replace('/[^A-Z]/', '', $message);
        return strlen($uppercase) / strlen($letters);
    }

    /**
     * Post a chat message
     */
    public function postMessage(
        LiveStream $stream,
        LiveSession $session,
        string $message,
        string $type = 'text'
    ): LiveChatMessage {
        // Analyze spam
        $spamAnalysis = $this->analyzeSpam($message);

        // Auto-hide if high spam score
        $status = $spamAnalysis['is_spam'] ? 'flagged' : 'visible';

        // Create message
        $chatMessage = LiveChatMessage::create([
            'stream_id' => $stream->id,
            'session_id' => $session->id,
            'user_id' => $session->user_id,
            'message' => $message,
            'type' => $type,
            'status' => $status,
            'spam_score' => $spamAnalysis['score'],
            'spam_flags' => $spamAnalysis['flags'],
            'ip_address' => request()->ip(),
        ]);

        // Increment counters
        $session->increment('messages_sent');
        $stream->increment('chat_messages_count');

        // Broadcast message
        if ($status === 'visible') {
            event(new \Modules\Live\Events\ChatMessageSent($chatMessage));
        }

        return $chatMessage;
    }

    /**
     * Get chat messages for a stream
     */
    public function getMessages(
        LiveStream $stream,
        int $limit = 100,
        ?string $beforeId = null
    ): Collection {
        $query = LiveChatMessage::where('stream_id', $stream->id)
            ->where('status', 'visible')
            ->with(['user', 'session'])
            ->orderBy('created_at', 'desc')
            ->limit($limit);

        if ($beforeId) {
            $query->where('id', '<', $beforeId);
        }

        return $query->get();
    }

    /**
     * Moderate a chat message
     */
    public function moderateMessage(
        LiveChatMessage $message,
        string $action,
        $moderator,
        ?string $reason = null
    ): bool {
        switch ($action) {
            case 'hide':
                $message->hide($moderator, $reason ?? 'Inappropriate content');
                return true;

            case 'delete':
                $message->update([
                    'status' => 'deleted',
                    'moderated_by' => $moderator->id,
                    'moderated_at' => now(),
                    'moderation_reason' => $reason ?? 'Deleted by moderator',
                ]);
                $message->delete();
                return true;

            case 'approve':
                $message->update([
                    'status' => 'visible',
                    'moderated_by' => $moderator->id,
                    'moderated_at' => now(),
                ]);
                event(new \Modules\Live\Events\ChatMessageSent($message));
                return true;

            default:
                return false;
        }
    }

    /**
     * Get flagged messages for moderation
     */
    public function getFlaggedMessages(int $limit = 50): Collection
    {
        return LiveChatMessage::where('status', 'flagged')
            ->with(['stream', 'user', 'session'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Clean up old chat messages
     */
    public function cleanupOldMessages(int $daysOld = 30): int
    {
        return LiveChatMessage::where('created_at', '<', now()->subDays($daysOld))
            ->whereHas('stream', function ($query) {
                $query->where('status', 'archived');
            })
            ->delete();
    }

    /**
     * Get chat statistics for a stream
     */
    public function getStreamChatStats(LiveStream $stream): array
    {
        return [
            'total_messages' => $stream->chat_messages_count,
            'active_participants' => LiveChatMessage::where('stream_id', $stream->id)
                ->distinct('user_id')
                ->count('user_id'),
            'flagged_messages' => LiveChatMessage::where('stream_id', $stream->id)
                ->where('status', 'flagged')
                ->count(),
            'average_spam_score' => LiveChatMessage::where('stream_id', $stream->id)
                ->avg('spam_score'),
            'messages_per_minute' => $this->calculateMessagesPerMinute($stream),
        ];
    }

    /**
     * Calculate messages per minute rate
     */
    protected function calculateMessagesPerMinute(LiveStream $stream): float
    {
        if (!$stream->started_at) {
            return 0;
        }

        $duration = $stream->ended_at
            ? $stream->started_at->diffInMinutes($stream->ended_at)
            : $stream->started_at->diffInMinutes(now());

        if ($duration === 0) {
            return 0;
        }

        return round($stream->chat_messages_count / $duration, 2);
    }
}
