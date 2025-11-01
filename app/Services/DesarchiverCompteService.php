<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Compte;

class DesarchiverCompteService
{
    public function desarchiverCompte(string $compteId): void
    {
        $local = DB::connection('pgsql'); // base principale
        $neon = DB::connection('neon');   // base d’archivage

        try {
            // 🔹 Récupération du compte archivé
            $compteArchive = $neon->table('comptes_bloque')->where('id', $compteId)->first();
            if (! $compteArchive) {
                Log::warning("Aucun compte archivé trouvé pour ID: {$compteId}");
                return;
            }

            $transactionsArchive = $neon->table('transactions_bloque')->where('compte_id', $compteId)->get();

            Log::info('[DÉSARCHIVAGE] Démarrage', [
                'compte_id' => $compteId,
                'source_db' => $neon->getDatabaseName(),
                'target_db' => $local->getDatabaseName(),
                'transactions' => $transactionsArchive->count(),
            ]);

            // 🔹 Insertion dans la base principale
            $local->transaction(function () use ($local, $compteArchive, $transactionsArchive) {
                // ✅ Réinsertion du compte
                if (! $local->table('comptes')->where('id', $compteArchive->id)->exists()) {
                    $local->table('comptes')->insert([
                        'id' => $compteArchive->id,
                        'numero_compte' => $compteArchive->numero_compte,
                        'type' => $compteArchive->type,
                        'solde_initial' => $compteArchive->solde_initial,
                        'devise' => $compteArchive->devise,
                        'statut' => 'actif',
                        'debut_blocage' => null,
                        'fin_blocage' => null,
                        'client_id' => $compteArchive->client_id,
                        'metadata' => $compteArchive->metadata,
                        'created_at' => $compteArchive->created_at,
                        'updated_at' => now(),
                    ]);
                }

                // ✅ Réinsertion des transactions
                foreach ($transactionsArchive as $t) {
                    $data = (array) $t;
                    unset($data['deleted_at']); // sécurité
                    if (! $local->table('transactions')->where('id', $t->id)->exists()) {
                        $local->table('transactions')->insert($data);
                    }
                }
            });

            // 🔹 Vérification
            $verifCompte = $local->table('comptes')->where('id', $compteId)->exists();
            $verifTransactions = $transactionsArchive->every(
                fn($t) => $local->table('transactions')->where('id', $t->id)->exists()
            );

            // 🔹 Suppression de Neon après succès
            if ($verifCompte && $verifTransactions) {
                $neon->transaction(function () use ($neon, $compteId) {
                    $neon->table('transactions_bloque')->where('compte_id', $compteId)->delete();
                    $neon->table('comptes_bloque')->where('id', $compteId)->delete();
                });
                Log::info("✅ Compte [{$compteId}] désarchivé et supprimé de la base d’archivage.");
            } else {
                Log::warning("Désarchivage incomplet pour le compte [{$compteId}].");
            }
        } catch (\Throwable $e) {
            Log::error("Erreur désarchivage compte [{$compteId}] : {$e->getMessage()}");
        }
    }
}
