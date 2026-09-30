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
        $this->info("Digest préparé pour {$result['recipients']} abonné(s). Envoi SMS non effectué.");
        $this->line($result['message']);

        return self::SUCCESS;
    }
}
