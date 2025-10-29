<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Jobs\DesarchiverCompteJob;

class VerifierDeblocageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        Log::info('[DESARCHIVAGE] Début du job de vérification', [
            'timestamp' => Carbon::now()->toDateTimeString(),
        ]);

        $neon = DB::connection('neon');

        // 🔹 Comptes archivés qui peuvent être désarchivés
        // Ici on suppose qu’on désarchive les comptes archivés depuis > X jours (ou autre critère)
        $criteriumDate = Carbon::now()->subDays(1); // exemple : archivés il y a plus d’1 jour
        $comptesArchives = $neon->table('comptes_bloque')
            ->where('created_at', '<=', $criteriumDate)
            ->get();

        Log::info('[DESARCHIVAGE] Comptes à désarchiver', [
            'total' => $comptesArchives->count(),
        ]);

        // 🔹 Dispatch des jobs de désarchivage
        foreach ($comptesArchives as $compte) {
            DesarchiverCompteJob::dispatch($compte->id)->onQueue('desarchivage');
            Log::info("📤 Dispatch du job de désarchivage", [
                'compte_id' => $compte->id,
                'numero_compte' => $compte->numero_compte
            ]);
        }
    }
}
