<?php

namespace App\Services;

use App\Models\Compte;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ArchiveCompteService
{
    /**
     * Archive un compte bloqué et ses transactions vers Neon.
     */
    public function archiverCompte(Compte $compte): void
    {
        $local = DB::connection('pgsql'); // base principale
        $neon = DB::connection('neon');   // base d’archivage

        try {
            $compte->load('transactions');

            Log::info('[ARCHIVAGE] Démarrage', [
                'compte_id' => $compte->id,
                'source_db' => $local->getDatabaseName(),
                'target_db' => $neon->getDatabaseName(),
                'transactions' => $compte->transactions->count(),
            ]);

            // 1️⃣ Insertion dans Neon
            $neon->transaction(function () use ($neon, $compte) {
                $exists = $neon->table('comptes_bloque')->where('id', $compte->id)->exists();
                if (! $exists) {
                    $neon->table('comptes_bloque')->insert([
                        'id' => $compte->id,
                        'numero_compte' => $compte->numero_compte,
                        'type' => $compte->type,
                        'solde_initial' => $compte->solde_initial,
                        'devise' => $compte->devise,
                        'statut' => $compte->statut,
                        'debut_blocage' => $compte->debut_blocage,
                        'fin_blocage' => $compte->fin_blocage,
                        'client_id' => $compte->client_id,
                        'metadata' => $compte->metadata,
                        'created_at' => $compte->created_at,
                        'updated_at' => now(),
                    ]);
                }

                foreach ($compte->transactions as $t) {
                    if (! $neon->table('transactions_bloque')->where('id', $t->id)->exists()) {
                        $neon->table('transactions_bloque')->insert($t->toArray());
                    }
                }
            });

            // 2️⃣ Vérification des insertions
            $verifCompte = $neon->table('comptes_bloque')->where('id', $compte->id)->exists();
            $verifTransactions = $compte->transactions->every(fn($t)
                => $neon->table('transactions_bloque')->where('id', $t->id)->exists()
            );

            // 3️⃣ Suppression locale après succès
            if ($verifCompte && $verifTransactions) {
                $local->transaction(function () use ($compte) {
                    $compte->transactions()->delete();
                    $compte->forceDelete();
                });
                Log::info("✅ Compte [{$compte->id}] archivé et supprimé localement.");
            } else {
                Log::warning("⚠️ Archivage incomplet pour le compte [{$compte->id}].");
            }

        } catch (\Throwable $e) {
            Log::error("❌ Erreur archivage compte [{$compte->id}] : {$e->getMessage()}");
        }
    }
}
