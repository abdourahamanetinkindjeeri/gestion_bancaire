<?php

namespace App\Console\Commands;

use App\Models\Transaction;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ArchiverTransactionsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'transactions:archive {--week= : La semaine à archiver (format: Y-W, ex: 2025-01)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Archive les transactions de la semaine passée vers MongoDB';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $week = $this->option('week');

        if ($week) {
            // Utiliser la semaine spécifiée
            $startOfWeek = Carbon::createFromFormat('Y-W', $week)->startOfWeek();
        } else {
            // Par défaut, archiver la semaine dernière
            $startOfWeek = Carbon::now()->subWeek()->startOfWeek();
        }

        $endOfWeek = $startOfWeek->copy()->endOfWeek();

        $this->info("Archivage des transactions de la semaine du {$startOfWeek->format('d/m/Y')} au {$endOfWeek->format('d/m/Y')}");

        try {
            // Récupérer les transactions de la semaine
            $transactions = Transaction::whereBetween('date_transaction', [$startOfWeek, $endOfWeek])
                ->with('compte.client.user')
                ->get();

            if ($transactions->isEmpty()) {
                $this->info('Aucune transaction à archiver pour cette semaine.');
                return;
            }

            $this->info("Nombre de transactions à archiver: {$transactions->count()}");

            // Créer le nom de la collection pour cette semaine
            $collectionName = 'transactions_' . $startOfWeek->format('Y_W');

            // Se connecter à MongoDB et archiver
            $this->archiveToMongoDB($transactions, $collectionName);

            // Supprimer les transactions archivées de PostgreSQL (optionnel)
            // $this->deleteArchivedTransactions($transactions);

            $this->info('Archivage terminé avec succès.');

        } catch (\Throwable $e) {
            $this->error("Erreur lors de l'archivage: " . $e->getMessage());
            Log::error("Erreur lors de l'archivage des transactions: " . $e->getMessage());
        }
    }

    /**
     * Archive les transactions vers MongoDB
     */
    private function archiveToMongoDB($transactions, string $collectionName)
    {
        // Utiliser la connexion MongoDB
        $mongodb = DB::connection('mongodb');

        // Créer la collection si elle n'existe pas
        $collection = $mongodb->getCollection($collectionName);

        $archivedData = [];

        foreach ($transactions as $transaction) {
            $archivedData[] = [
                'original_id' => $transaction->id,
                'numero' => $transaction->numero,
                'compte_id' => $transaction->compte_id,
                'numero_compte' => $transaction->compte->numero_compte,
                'client_id' => $transaction->compte->client_id,
                'client_email' => $transaction->compte->client->user->email,
                'client_telephone' => $transaction->compte->client->user->telephone,
                'type' => $transaction->type,
                'montant' => $transaction->montant,
                'devise' => $transaction->devise,
                'statut' => $transaction->statut,
                'date_transaction' => $transaction->date_transaction->toISOString(),
                'metadata' => $transaction->metadata,
                'archived_at' => now()->toISOString(),
                'created_at' => $transaction->created_at->toISOString(),
                'updated_at' => $transaction->updated_at->toISOString(),
            ];
        }

        // Insérer en batch
        $collection->insertMany($archivedData);

        $this->info("Transactions archivées dans la collection: {$collectionName}");
    }

    /**
     * Supprime les transactions archivées de PostgreSQL (optionnel)
     */
    private function deleteArchivedTransactions($transactions)
    {
        $ids = $transactions->pluck('id');
        Transaction::whereIn('id', $ids)->delete();

        $this->info("Transactions supprimées de PostgreSQL: {$ids->count()}");
    }
}
