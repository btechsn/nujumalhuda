<?php

declare(strict_types=1);

namespace Modules\Core\Actions;

use Illuminate\Support\Str;
use Modules\Core\Events\MinorAnonymized;
use Modules\Core\Models\AuditLog;
use Modules\Core\Models\User;

/**
 * Cahier des charges, §8.3, point 4 : « Droit à l'effacement outillé : une
 * procédure d'anonymisation d'un élève, y compris dans les archives VOD. »
 *
 * On anonymise plutôt que de supprimer : les enregistrements qui pointent
 * vers cet utilisateur (inscriptions, attestations, progression, écritures
 * de trésorerie...) doivent rester cohérents. La ligne `users` est
 * conservée, vidée de ce qui identifie la personne.
 */
class AnonymizeMinorAction
{
    public function execute(User $user): User
    {
        $suffix = Str::upper(Str::random(6));

        $user->forceFill([
            'first_name' => 'Élève',
            'last_name' => 'anonymisé-'.$suffix,
            // La table `users` exige email OU téléphone : une adresse
            // factice et non joignable remplace l'identité, sans violer
            // cette contrainte.
            'email' => 'anonymise-'.Str::lower($suffix).'@anonymise.local',
            'phone' => null,
            'avatar' => null,
            'password' => Str::password(40),
            'email_verified_at' => null,
            'phone_verified_at' => null,
            'anonymized_at' => now(),
        ])->save();

        $user->tokens()->delete();

        // Les consentements n'ont plus de sens pour une identité effacée :
        // on referme tous les usages pour empêcher toute nouvelle diffusion.
        $user->minorConsent()->update([
            'gallery_image_consent' => false,
            'recitation_audio_consent' => false,
            'public_name_consent' => false,
            'notes' => 'Élève anonymisé le '.now()->format('d/m/Y').'.',
        ]);

        $fresh = $user->fresh();

        // Identifiant seul : le journal ne doit pas réécrire le nom, l'e-mail
        // ou le téléphone que l'on vient d'effacer.
        AuditLog::log('minor.anonymized', $fresh, context: ['user_id' => $fresh->id]);

        event(new MinorAnonymized($fresh));

        return $fresh;
    }
}
