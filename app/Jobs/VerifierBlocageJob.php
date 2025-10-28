<?php

namespace App\Jobs;

use App\Models\Compte;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Jobs\ArchiverCompteJob;

class VerifierBlocageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $comptes = Compte::where('statut', 'bloque')
            ->where('debut_blocage', '<=', Carbon::now()->subDays(3))
            ->get();

        Log::info("🔎 Vérification des comptes bloqués ({$comptes->count()}) à archiver...");

        foreach ($comptes as $compte) {
            ArchiverCompteJob::dispatch($compte->id)->onQueue('archivage');
        }
    }
}
