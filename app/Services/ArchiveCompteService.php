<?php

namespace App\Services;

use App\Models\Compte;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ArchiveCompteService
{
    public function archiverCompte(string $compteId): void
    {
        $local = DB::connection('pgsql'); // base principale
        $neon = DB::connection('neon');   // base d’archivage

        try {
            $compte = Compte::with('transactions')->findOrFail($compteId);

            Log::info('[ARCHIVAGE] Démarrage', [
                'compte_id' => $compte->id,
                'source_db' => $local->getDatabaseName(),
                'target_db' => $neon->getDatabaseName(),
                'transactions' => $compte->transactions->count(),
            ]);

            // 🔹 Insertion dans Neon
            $neon->transaction(function () use ($neon, $compte) {
                if (! $neon->table('comptes_bloque')->where('id', $compte->id)->exists()) {
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
                        'metadata' => json_encode($compte->metadata ?? []),
                        'created_at' => $compte->created_at,
                        'updated_at' => now(),
                    ]);
                }

                foreach ($compte->transactions as $t) {
                    $data = $t->toArray();
                    unset($data['deleted_at']); // sécurité si colonne manquante
                    if (! $neon->table('transactions_bloque')->where('id', $t->id)->exists()) {
                        $neon->table('transactions_bloque')->insert($data);
                    }
                }
            });

            // 🔹 Vérification
            $verifCompte = $neon->table('comptes_bloque')->where('id', $compte->id)->exists();
            $verifTransactions = $compte->transactions->every(
                fn($t)
                => $neon->table('transactions_bloque')->where('id', $t->id)->exists()
            );

            // 🔹 Suppression locale
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
