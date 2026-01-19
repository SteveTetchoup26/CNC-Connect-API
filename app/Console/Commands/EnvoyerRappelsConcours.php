<?php

namespace App\Console\Commands;

use App\Jobs\EnvoyerRappelsConcours as EnvoyerRappelsConcoursJob;
use Illuminate\Console\Command;

class EnvoyerRappelsConcours extends Command
{
    protected $signature = 'rappels:envoyer';
    protected $description = 'Envoyer les rappels de concours programmés';

    public function handle()
    {
        $this->info('Envoi des rappels de concours...');

        EnvoyerRappelsConcoursJob::dispatch();

        $this->info('Rappels envoyés avec succès !');
    }
}
