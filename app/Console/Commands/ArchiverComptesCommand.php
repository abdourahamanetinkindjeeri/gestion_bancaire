<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\VerifierBlocageJob;

class ArchiverComptesCommand extends Command
{
    protected $signature = 'comptes:archiver';
    protected $description = 'Vérifie et archive les comptes bloqués depuis 3 jours';

    public function handle(): void
    {
        VerifierBlocageJob::dispatch();
        $this->info('🚀 Vérification des comptes bloqués lancée.');
    }
}
