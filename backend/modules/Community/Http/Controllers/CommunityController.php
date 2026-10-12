<?php

namespace Modules\Community\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Community\Models\Sponsorship;
use Modules\Core\Models\User;
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
use Modules\Core\Support\ContributionDesk;
use Modules\Core\Support\EditorialText;

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
                'name' => $donation->is_anonymous ? null : ($donation->donor_name ?: null),
                'is_anonymous' => (bool) $donation->is_anonymous,
                'amount_minor' => $donation->is_anonymous ? null : $donation->amount_minor,
                'currency' => $donation->currency,
                'paid_at' => optional($donation->paid_at)->toISOString(),
            ]);
    }

    public function donate(Request $request, DonationCheckout $checkout)
    {
        if ($user = $request->user('sanctum')) {
            $request->setUserResolver(fn () => $user);
        }

        $result = $checkout->start($request);

        return response()->json($result['body'], $result['status']);
    }

    /**
     * Élèves dont le nom peut être publié pour un parrainage.
     */
    public function sponsorableStudents()
    {
        return response()->json([
            'data' => $this->eligibleStudents()->map(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->fullName(),
            ])->values(),
        ]);
    }

    /**
     * Engagement de parrainage. Le secrétariat confirme avant qu'il soit actif.
     */
    public function sponsor(Request $request)
    {
        $data = $request->validate([
            'student_id' => ['nullable', 'string', 'size:26'],
            'amount_minor' => ['required', 'integer', 'min:100', 'max:100000000'],
            'frequency' => ['required', 'in:once,monthly,yearly'],
        ]);

        $studentId = $data['student_id'] ?? null;
        if ($studentId && ! $this->eligibleStudents()->contains(fn (User $user) => $user->id === $studentId)) {
            return response()->json([
                'message' => 'Cet élève ne peut pas être désigné publiquement.',
            ], 422);
        }

        $sponsorship = Sponsorship::query()->create([
            'sponsor_id' => $request->user()->id,
            'student_id' => $studentId,
            'amount_minor' => $data['amount_minor'],
            'currency' => 'XOF',
            'frequency' => $data['frequency'],
            'status' => 'pending',
        ]);

        return response()->json([
            'id' => $sponsorship->id,
            'status' => $sponsorship->status,
            'message' => 'Demande de parrainage enregistrée. Le secrétariat la confirmera.',
        ], 201);
    }

    /**
     * @return Collection<int, User>
     */
    private function eligibleStudents(): Collection
    {
        $ids = DB::table('enrollments')
            ->whereIn('status', ['approved', 'active', 'completed'])
            ->whereNull('deleted_at')
            ->distinct()
            ->pluck('user_id');

        return User::query()
            ->with(['minorConsent', 'guardians'])
            ->whereIn('id', $ids)
            ->whereNull('anonymized_at')
            ->orderBy('first_name')
            ->get()
            ->filter(function (User $user): bool {
                if ($user->fullName() === '') {
                    return false;
                }

                return $user->guardians->isEmpty()
                    || (bool) $user->minorConsent?->public_name_consent;
            })
            ->values();
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
                    'caption' => EditorialText::present($item->caption_i18n, $locale)['text'],
                    'caption_fell_back' => EditorialText::present($item->caption_i18n, $locale)['fell_back'],
                    'event_name' => $item->event_name,
                    'media_url' => $item->publicMediaUrl(),
                    'thumbnail_url' => $item->publicThumbnailUrl() ?: $item->publicMediaUrl(),
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
            ->with(['asker:id,first_name,last_name', 'teacher:id,first_name,last_name'])
            ->where('status', 'published')
            ->where('is_public', true)
            ->latest('created_at')
            ->latest('id')
            ->paginate($perPage)
            ->through(function (Question $question) use ($locale) {
                $teacher = $question->teacherName();
                $answered = $question->publicAnswer($locale) !== null;
                $title = EditorialText::present($question->question_i18n, $locale);

                return [
                    'id' => $question->id,
                    'title' => $title['text'],
                    'title_fell_back' => $title['fell_back'],
                    'author' => trim(($question->asker?->first_name ?? '').' '.($question->asker?->last_name ?? '')) ?: 'Membre',
                    'teacher' => $answered ? $teacher : null,
                    'replies' => $answered ? 1 : 0,
                    'answered' => $answered,
                    'topics' => $question->topics ?? [],
                    'created_at' => optional($question->created_at)->toISOString(),
                    'answered_at' => $answered ? optional($question->answered_at)->toISOString() : null,
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

        $answer = $question->publicAnswer($locale);
        $title = EditorialText::present($question->question_i18n, $locale);
        $answerLine = EditorialText::present($question->answer_i18n, $locale);

        return response()->json([
            'data' => [
                'id' => $question->id,
                'title' => $title['text'],
                'title_fell_back' => $title['fell_back'],
                'answer' => $answer,
                'answer_fell_back' => $answer !== null && $answerLine['fell_back'],
                'author' => trim(($question->asker?->first_name ?? '').' '.($question->asker?->last_name ?? '')) ?: 'Membre',
                'teacher' => $answer !== null ? $question->teacherName() : null,
                'replies' => $answer !== null ? 1 : 0,
                'answered' => $answer !== null,
                'topics' => $question->topics ?? [],
                'answered_at' => $answer !== null ? optional($question->answered_at)->toISOString() : null,
            ],
        ]);
    }

    public function ask(Request $request)
    {
        if ($closed = ContributionDesk::closedResponse('questions')) {
            return $closed;
        }

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
