<?php

declare(strict_types=1);

namespace Modules\Core\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Modules\Core\Actions\RegisterUserAction;
use Modules\Core\Data\UserData;
use Modules\Core\Http\Requests\LoginRequest;
use Modules\Core\Http\Requests\RegisterRequest;
use Modules\Core\Http\Resources\UserResource;
use Modules\Core\Models\User;
use Modules\Core\Services\PasswordResetService;
use Modules\Core\Support\PhoneNumber;

class AuthController extends Controller
{
    public function __construct(
        private readonly RegisterUserAction $registerUserAction
    ) {
    }

    /**
     * Inscription d'un nouvel utilisateur.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $userData = UserData::from([
            'id' => null,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'locale' => $request->locale ?? 'fr',
            'timezone' => $request->timezone ?? 'Africa/Dakar',
            'avatar' => null,
        ]);

        $user = $this->registerUserAction->execute($userData, $request->password);

        $token = $user->createToken('auth_token')->plainTextToken;

        return ApiResponse::created([
            'user' => UserResource::make($user),
            'token' => $token,
        ], 'Inscription réussie');
    }

    /**
     * Connexion utilisateur.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $phones = PhoneNumber::storedForms($request->phone);
        $user = User::query()
            ->whereNull('disabled_at')
            ->where(function ($query) use ($request, $phones): void {
                if ($request->filled('email')) {
                    $query->orWhere('email', $request->email);
                }
                if ($phones !== []) {
                    $query->orWhereIn('phone', $phones);
                }
            })
            ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'credentials' => ['Les identifiants sont incorrects.'],
            ]);
        }

        $user->update(['last_login_at' => now()]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return ApiResponse::success([
            'user' => UserResource::make($user),
            'token' => $token,
        ], 'Connexion réussie');
    }

    /**
     * Déconnexion utilisateur.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return ApiResponse::success(null, 'Déconnexion réussie');
    }

    /**
     * Récupère l'utilisateur authentifié.
     */
    public function user(Request $request): JsonResponse
    {
        return ApiResponse::success(
            UserResource::make($request->user())
        );
    }

    /**
     * Le membre met à jour son propre profil (prénom, nom, téléphone, langue).
     * L'e-mail reste inchangé : le changer exigerait une nouvelle vérification.
     */
    public function updateProfile(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        if ($user->anonymized_at !== null) {
            return ApiResponse::error('Ce compte a été anonymisé.', 403);
        }

        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:20'],
            'locale' => ['required', 'string', 'in:fr,en,ar'],
        ]);

        $rawPhone = trim((string) ($data['phone'] ?? ''));
        $phone = $rawPhone === '' ? null : PhoneNumber::e164($rawPhone);

        if ($rawPhone !== '' && $phone === null) {
            throw ValidationException::withMessages([
                'phone' => 'Le numéro de téléphone n\'est pas valide.',
            ]);
        }

        if ($phone === null && blank($user->email)) {
            throw ValidationException::withMessages([
                'phone' => 'Un téléphone est requis : ce compte n\'a pas d\'adresse e-mail.',
            ]);
        }

        if ($phone !== null && User::query()->where('phone', $phone)->whereKeyNot($user->id)->exists()) {
            throw ValidationException::withMessages([
                'phone' => 'Ce numéro de téléphone est déjà utilisé.',
            ]);
        }

        $phoneChanged = $phone !== $user->phone;

        $user->forceFill([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'phone' => $phone,
            'locale' => $data['locale'],
            'phone_verified_at' => $phoneChanged ? null : $user->phone_verified_at,
        ])->save();

        return ApiResponse::success(
            UserResource::make($user->fresh()),
            'Profil mis à jour'
        );
    }

    public function forgotPassword(Request $request, PasswordResetService $resets): JsonResponse
    {
        $data = $request->validate([
            'identifier' => 'required|string|max:180',
        ]);

        $link = $resets->request($data['identifier']);

        return ApiResponse::success(
            $link ? ['reset_url' => $link] : null,
            'Si un compte correspond, un message de réinitialisation a été envoyé.',
        );
    }

    public function resetPassword(Request $request, PasswordResetService $resets): JsonResponse
    {
        $data = $request->validate([
            'identifier' => 'required|string|max:180',
            'token' => 'required|string|max:80',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $ok = $resets->reset($data['identifier'], $data['token'], $data['password']);
        if (!$ok) {
            return ApiResponse::error('Le code est invalide ou expiré.', 422);
        }

        return ApiResponse::success(null, 'Mot de passe mis à jour.');
    }
}
