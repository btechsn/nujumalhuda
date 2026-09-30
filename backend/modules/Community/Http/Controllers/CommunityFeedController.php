<?php

namespace Modules\Community\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Community\Models\CommunityEvent;
use Modules\Community\Models\Question;
use Modules\Community\Models\Testimonial;
use Modules\Core\Models\Membership;
use Modules\Dahira\Models\DahiraGroup;
use Modules\Education\Models\Teacher;

class CommunityFeedController extends Controller
{
    public function board()
    {
        $locale = app()->getLocale();

        $discussions = Question::query()
            ->with('asker:id,first_name,last_name')
            ->where('status', 'published')
            ->where('is_public', true)
            ->latest('created_at')
            ->latest('id')
            ->limit(12)
            ->get()
            ->map(fn (Question $q) => [
                'id' => $q->id,
                'title' => $q->question_i18n[$locale] ?? $q->question_i18n['fr'] ?? '',
                'author' => trim(($q->asker?->first_name ?? '').' '.($q->asker?->last_name ?? '')) ?: 'Membre',
                'replies' => $q->answer_i18n ? 1 : 0,
                'answered' => (bool) $q->answer_i18n,
                'topics' => $q->topics ?? [],
                'created_at' => optional($q->created_at)->toISOString(),
                'answered_at' => optional($q->answered_at)->toISOString(),
            ]);

        $groups = DahiraGroup::query()
            ->where('is_active', true)
            ->latest('founded_on')
            ->latest('id')
            ->limit(8)
            ->get()
            ->map(function (DahiraGroup $group) use ($locale) {
                $members = Membership::query()
                    ->where('organization_id', $group->organization_id)
                    ->where('status', 'active')
                    ->count();

                return [
                    'id' => $group->id,
                    'name' => $group->name_i18n[$locale] ?? $group->name_i18n['fr'] ?? '',
                    'description' => $group->description_i18n[$locale] ?? $group->description_i18n['fr'] ?? '',
                    'location' => $group->location,
                    'members' => $members,
                    'meeting_weekday' => $group->meeting_weekday,
                ];
            });

        $event = CommunityEvent::query()
            ->where('is_published', true)
            ->where('starts_at', '>=', now()->subHour())
            ->orderBy('starts_at')
            ->first();

        $featuredEvent = $event ? [
            'id' => $event->id,
            'title' => $event->title_i18n[$locale] ?? $event->title_i18n['fr'] ?? '',
            'description' => $event->description_i18n[$locale] ?? $event->description_i18n['fr'] ?? '',
            'starts_at' => $event->starts_at?->toISOString(),
            'ends_at' => $event->ends_at?->toISOString(),
            'location' => $event->location,
            'capacity' => $event->capacity,
        ] : null;

        $topics = $discussions
            ->pluck('topics')
            ->flatten()
            ->filter()
            ->countBy()
            ->sortDesc()
            ->keys()
            ->take(12)
            ->values();

        $people = Teacher::query()
            ->with(['user:id,first_name,last_name', 'photo'])
            ->available()
            ->ordered()
            ->limit(5)
            ->get()
            ->map(function (Teacher $teacher) use ($locale) {
                $availability = is_array($teacher->availability) ? $teacher->availability : [];
                $title = $availability['title_i18n'] ?? [];
                $role = is_array($title)
                    ? ($title[$locale] ?? $title['fr'] ?? '')
                    : (string) $title;

                return [
                    'id' => $teacher->id,
                    'name' => trim(($teacher->user?->first_name ?? '').' '.($teacher->user?->last_name ?? '')),
                    'role' => $role,
                    'photo_url' => $teacher->photo?->url()
                        ?? ($availability['photo'] ?? null),
                ];
            })
            ->filter(fn ($p) => $p['name'] !== '')
            ->values();

        if ($people->isEmpty()) {
            $people = Testimonial::query()
                ->where('status', 'approved')
                ->latest()
                ->limit(5)
                ->get()
                ->map(fn (Testimonial $t) => [
                    'id' => $t->id,
                    'name' => $t->author_name,
                    'role' => $t->relation,
                    'photo_url' => null,
                ]);
        }

        return response()->json([
            'discussions' => $discussions,
            'groups' => $groups,
            'featured_event' => $featuredEvent,
            'topics' => $topics,
            'people' => $people,
        ]);
    }
}
