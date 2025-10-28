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
        DB::transaction(function () use ($compte) {
            $neon = DB::connection('neon');

            try {
                $compte->load('transactions');

                // Vérifier si le compte existe déjà dans Neon
                $existing = $neon->table('comptes_bloque')->where('id', $compte->id)->first();
                if (!$existing) {
                    // Insérer le compte dans la base Neon
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
                } else {
                    Log::info("ℹ️ Compte [{$compte->id}] déjà archivé dans Neon.");
                }

                // Insérer les transactions associées (seulement si elles n'existent pas)
                foreach ($compte->transactions as $transaction) {
                    $existingTransaction = $neon->table('transactions_bloque')->where('id', $transaction->id)->first();
                    if (!$existingTransaction) {
                        $neon->table('transactions_bloque')->insert($transaction->toArray());
                    }
                }

                // Vérification post-insertion
                $verifCompte = $neon->table('comptes_bloque')->where('id', $compte->id)->first();
                $verifTransactions = $compte->transactions->every(function ($transaction) use ($neon) {
                    return $neon->table('transactions_bloque')->where('id', $transaction->id)->exists();
                });

                if ($verifCompte && $verifTransactions) {
                    // Suppression locale après archivage réussi
                    $compte->transactions()->delete();
                    $compte->forceDelete();
                    Log::info("✅ Compte [{$compte->id}] archivé et supprimé localement.");
                } else {
                    Log::error("❌ Archivage incomplet pour le compte [{$compte->id}] : insertion non vérifiée.");
                }
            } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
                Log::info("ℹ️ Compte [{$compte->id}] déjà archivé, suppression locale uniquement.");
                $compte->transactions()->delete();
                $compte->forceDelete();
                Log::info("✅ Compte [{$compte->id}] supprimé localement après archivage existant.");
            } catch (\Throwable $e) {
                Log::error("❌ Erreur lors de l’archivage du compte [{$compte->id}] : {$e->getMessage()}");
                // Pas de suppression locale en cas d'erreur
            }
        });
    }
}
