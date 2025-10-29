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
        Log::info('[ARCHIVAGE] Début du job de vérification', [
            'timestamp' => Carbon::now()->toDateTimeString(),
        ]);

        // 🔹 Déblocage automatique des comptes expirés
        $comptesExpires = Compte::whereIn('statut', ['bloque', 'suspendu'])
            ->where('fin_blocage', '<=', Carbon::now())
            ->get();

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

            Log::info("✅ Compte débloqué automatiquement", [
                'compte_id' => $compte->id,
                'numero_compte' => $compte->numero_compte
            ]);
        }

        // 🔹 Récupération des comptes à archiver
        // Seuls les comptes épargne bloqués dont la date de début de blocage est échue peuvent être archivés
        $comptesBloques = Compte::where('statut', 'bloque')
            ->where('type', 'epargne')
            ->where('debut_blocage', '<=', Carbon::now())
            ->get();
        $comptesSuspendus = Compte::where('statut', 'suspendu')
            ->where('type', 'epargne')
            ->where('debut_blocage', '<=', Carbon::now())
            ->get();
        $comptes = $comptesBloques->merge($comptesSuspendus);

        Log::info('[ARCHIVAGE] Comptes à archiver', [
            'bloques' => $comptesBloques->count(),
            'suspendus' => $comptesSuspendus->count(),
            'total' => $comptes->count(),
        ]);

        // 🔹 Dispatch des jobs d’archivage
        foreach ($comptes as $compte) {
            ArchiverCompteJob::dispatch($compte->id)->onQueue('archivage');
        }
    }
}
