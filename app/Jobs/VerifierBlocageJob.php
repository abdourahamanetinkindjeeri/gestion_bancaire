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
        Log::info('[ARCHIVAGE] Début du job de vérification des comptes à archiver', [
            'now' => Carbon::now()->toDateTimeString(),
            'timezone' => Carbon::now()->timezoneName,
        ]);
        // 🔹 1. Débloquer automatiquement les comptes expirés
        $comptesExpires = Compte::whereIn('statut', ['bloque', 'suspendu'])
            ->where('fin_blocage', '<=', Carbon::now())
            ->get();

        Log::info("🔄 Déblocage automatique de {$comptesExpires->count()} comptes expirés...");

        foreach ($comptesExpires as $compte) {
            $compte->update([
                'statut' => 'actif',
                'debut_blocage' => null,
                'fin_blocage' => null,
                'metadata' => array_merge($compte->metadata ?? [], [
                    'date_deblocage_automatique' => Carbon::now()->toISOString(),
                    'motif_deblocage' => 'Expiration période de blocage',
                ])
            ]);

            Log::info("✅ Compte {$compte->numero_compte} débloqué automatiquement");
        }

        // 🔹 2. Archiver les comptes bloqués/suspendus (sans condition de date pour les suspendus)
        $comptesBloques = Compte::where('statut', 'bloque')
            ->where('debut_blocage', '<=', Carbon::now())
            ->get();

        $comptesSuspendus = Compte::where('statut', 'suspendu')->get();

        $comptes = $comptesBloques->merge($comptesSuspendus);

        Log::info('[ARCHIVAGE] Comptes à archiver trouvés', [
            'comptes_bloques' => $comptesBloques->count(),
            'comptes_suspendus' => $comptesSuspendus->count(),
            'total' => $comptes->count(),
            'now' => Carbon::now()->toDateTimeString(),
        ]);
        Log::info("🔎 Vérification des comptes bloqués ({$comptes->count()}) à archiver...");

        foreach ($comptes as $compte) {
            ArchiverCompteJob::dispatch($compte->id)->onQueue('archivage');
        }
    }
}
    