<?php

namespace Modules\Community\Console\Commands;

use Illuminate\Console\Command;
use Modules\Community\Services\SmsDigestService;

class PrepareSmsDigestCommand extends Command
{
    protected $signature = 'community:prepare-digest';

    protected $description = 'Prépare le digest SMS mensuel sans l\'envoyer';

    public function handle(SmsDigestService $digests): int
    {
        $result = $digests->prepare();
        if (($result['gateway'] ?? '') === 'not_configured') {
            $this->warn('Orange SMS n’est pas configuré : aucun message n’est parti. '.$result['recipients'].' destinataire(s) restent en attente.');
        } else {
            $this->info($result['sent'].' SMS envoyé(s) sur '.$result['recipients'].'.');
        }
        $this->line($result['message']);

        return self::SUCCESS;
    }
}
