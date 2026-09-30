<?php

namespace Modules\Community\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Modules\Community\Models\CommunityEvent;
use Modules\Community\Models\ContactMessage;
use Modules\Community\Models\Donation;
use Modules\Community\Services\DonationCheckout;
use Modules\Community\Models\EventRegistration;
use Modules\Community\Models\NewsletterSubscriber;
use Modules\Community\Models\GalleryItem;
use Modules\Community\Models\Question;
use Modules\Community\Models\Partner;
use Modules\Community\Models\SmsDigestSubscriber;
use Modules\Community\Models\Testimonial;

class CommunityController extends Controller
{
    public function donors()
    {
        return Donation::query()
            ->where('status', 'completed')
            ->latest('paid_at')
            ->limit(50)
            ->get()
            ->map(fn (Donation $donation) => [
                'name' => $donation->publicName(),
                'amount_minor' => $donation->is_anonymous ? null : $donation->amount_minor,
                'currency' => $donation->currency,
                'paid_at' => optional($donation->paid_at)->toISOString(),
            ]);
    }

    public function donate(Request $request, DonationCheckout $checkout)
    {
        $result = $checkout->start($request);

        return response()->json($result['body'], $result['status']);
    }

    public function testimonials()
    {
        return Testimonial::query()->where('status', 'approved')->latest()->paginate(12);
    }

    public function partners()
    {
        $locale = app()->getLocale();

        $items = Partner::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(fn (Partner $partner) => [
                'id' => $partner->id,
                'name' => $partner->name,
                'description' => $partner->description_i18n[$locale]
                    ?? $partner->description_i18n['fr']
                    ?? '',
                'logo_url' => $partner->logo_url,
                'website_url' => $partner->website_url,
            ])
            ->values();

        return response()->json(['data' => $items]);
    }

    public function gallery(Request $request)
    {
        $locale = app()->getLocale();
        $kind = $request->query('kind');
        $q = trim((string) $request->query('q', ''));
        $perPage = max(1, min(48, (int) $request->integer('per_page', 12)));

        $items = GalleryItem::query()
            ->where('is_public', true)
            ->when(in_array($kind, ['photo', 'video'], true), fn ($query) => $query->where('kind', $kind))
            ->when($q !== '', function ($query) use ($q) {
                $like = '%'.$q.'%';
                $query->where(function ($inner) use ($like) {
                    $inner->where('event_name', 'ilike', $like)
                        ->orWhere('caption_i18n->fr', 'ilike', $like)
                        ->orWhere('caption_i18n->en', 'ilike', $like)
                        ->orWhere('caption_i18n->ar', 'ilike', $like);
                });
            })
            ->latest('taken_on')
            ->latest('id')
            ->paginate($perPage)
            ->through(function (GalleryItem $item) use ($locale) {
                return [
                    'id' => $item->id,
                    'kind' => $item->kind,
                    'caption' => $item->caption_i18n[$locale] ?? $item->caption_i18n['fr'] ?? '',
                    'event_name' => $item->event_name,
                    'media_url' => $item->media_url,
                    'thumbnail_url' => $item->thumbnail_url ?: $item->media_url,
                    'taken_on' => optional($item->taken_on)->toDateString(),
                ];
            });

        return response()->json($items);
    }

    public function questions(Request $request)
    {
        $locale = app()->getLocale();
        $perPage = max(1, min(48, (int) $request->integer('per_page', 12)));

        $items = Question::query()
            ->with('asker:id,first_name,last_name')
            ->where('status', 'published')
            ->where('is_public', true)
            ->latest('created_at')
            ->latest('id')
            ->paginate($perPage)
            ->through(function (Question $question) use ($locale) {
                return [
                    'id' => $question->id,
                    'title' => $question->question_i18n[$locale] ?? $question->question_i18n['fr'] ?? '',
                    'author' => trim(($question->asker?->first_name ?? '').' '.($question->asker?->last_name ?? '')) ?: 'Membre',
                    'replies' => $question->answer_i18n ? 1 : 0,
                    'answered' => (bool) $question->answer_i18n,
                    'topics' => $question->topics ?? [],
                    'created_at' => optional($question->created_at)->toISOString(),
                    'answered_at' => optional($question->answered_at)->toISOString(),
                ];
            });

        return response()->json($items);
    }

    public function question(string $id)
    {
        $locale = app()->getLocale();
        $question = Question::query()
            ->with(['asker:id,first_name,last_name', 'teacher:id,first_name,last_name'])
            ->where('status', 'published')
            ->where('is_public', true)
            ->findOrFail($id);

        return response()->json([
            'data' => [
                'id' => $question->id,
                'title' => $question->question_i18n[$locale] ?? $question->question_i18n['fr'] ?? '',
                'answer' => $question->answer_i18n[$locale] ?? $question->answer_i18n['fr'] ?? null,
                'author' => trim(($question->asker?->first_name ?? '').' '.($question->asker?->last_name ?? '')) ?: 'Membre',
                'teacher' => trim(($question->teacher?->first_name ?? '').' '.($question->teacher?->last_name ?? '')) ?: null,
                'replies' => $question->answer_i18n ? 1 : 0,
                'answered' => (bool) $question->answer_i18n,
                'topics' => $question->topics ?? [],
                'answered_at' => optional($question->answered_at)->toISOString(),
            ],
        ]);
    }

    public function ask(Request $request)
    {
        $data = $request->validate([
            'question_i18n.fr' => 'required|string|max:2000',
            'question_i18n.en' => 'nullable|string|max:2000',
            'question_i18n.ar' => 'nullable|string|max:2000',
        ]);

        $question = Question::create([
            'asker_id' => $request->user()->id,
            'question_i18n' => $data['question_i18n'],
            'status' => 'pending',
            'is_public' => false,
        ]);

        return response()->json($question, 201);
    }

    public function events()
    {
        return CommunityEvent::query()
            ->where('is_published', true)
            ->where('starts_at', '>=', now()->subDay())
            ->orderBy('starts_at')
            ->paginate(20);
    }

    public function register(Request $request, string $eventId)
    {
        $event = CommunityEvent::query()->where('is_published', true)->findOrFail($eventId);

        if ($event->capacity && $event->registrations()->where('status', 'confirmed')->count() >= $event->capacity) {
            return response()->json(['message' => 'Complet'], 422);
        }

        $data = $request->validate([
            'full_name' => 'nullable|string|max:120',
            'first_name' => 'nullable|string|max:60',
            'last_name' => 'nullable|string|max:60',
            'phone' => 'required|string|max:20',
        ]);

        $fullName = trim((string) ($data['full_name'] ?? ''));
        if ($fullName === '') {
            $fullName = trim(($data['first_name'] ?? '').' '.($data['last_name'] ?? ''));
        }

        if ($fullName === '') {
            return response()->json(['message' => 'Le nom est requis.'], 422);
        }

        $registration = EventRegistration::updateOrCreate(
            ['event_id' => $event->id, 'phone' => $data['phone']],
            [
                'user_id' => $request->user()?->id,
                'full_name' => $fullName,
                'confirmation_code' => strtoupper(Str::random(8)),
                'status' => 'confirmed',
            ]
        );

        return response()->json([
            'status' => $registration->status,
            'confirmation_code' => $registration->confirmation_code,
        ], 201);
    }

    public function subscribeDigest(Request $request)
    {
        $data = $request->validate([
            'phone' => 'required|string|max:20',
            'locale' => 'nullable|in:fr,en,ar',
            'consent' => 'accepted',
        ]);

        $subscriber = SmsDigestSubscriber::updateOrCreate(
            ['phone' => $data['phone']],
            [
                'locale' => $data['locale'] ?? 'fr',
                'consented_at' => now(),
                'is_active' => true,
            ]
        );

        return response()->json([
            'id' => $subscriber->id,
            'message' => 'Inscription au digest SMS enregistrée.',
        ], 201);
    }

    public function subscribeNewsletter(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email|max:255',
            'locale' => 'nullable|in:fr,en,ar',
        ]);

        NewsletterSubscriber::updateOrCreate(
            ['email' => strtolower($data['email'])],
            ['locale' => $data['locale'] ?? 'fr'],
        );

        return response()->json(['message' => 'ok'], 201);
    }

    public function contact(Request $request)
    {
        $data = $request->validate([
            'first_name' => 'required|string|max:60',
            'last_name' => 'required|string|max:60',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'subject' => 'required|string|max:160',
            'message' => 'required|string|max:4000',
            'locale' => 'nullable|in:fr,en,ar',
        ]);

        if (empty($data['email']) && empty($data['phone'])) {
            return response()->json(['message' => 'Email ou téléphone requis.'], 422);
        }

        $message = ContactMessage::query()->create([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => isset($data['email']) ? strtolower($data['email']) : null,
            'phone' => $data['phone'] ?? null,
            'subject' => $data['subject'],
            'message' => $data['message'],
            'locale' => $data['locale'] ?? app()->getLocale(),
            'status' => 'new',
        ]);

        return response()->json([
            'id' => $message->id,
            'message' => 'Message reçu.',
        ], 201);
    }
}
