<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\Compte;
use App\Models\User;
use App\Repositories\TransactionRepository;
use App\Services\NotificationManager;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use MongoDB\Client;

class TransactionService extends BaseService
{
    protected NotificationManager $notificationManager;
    protected TransactionRepository $transactionRepository;

    public function __construct(TransactionRepository $transactionRepository, NotificationManager $notificationManager)
    {
        $this->transactionRepository = $transactionRepository;
        $this->notificationManager = $notificationManager;
    }

    /**
     * Récupérer toutes les transactions avec filtres
     */
    public function getAll(array $filters = [], int $page = 1, int $limit = 10): LengthAwarePaginator
    {
        return $this->transactionRepository->all($filters, $page, $limit);
    }

    /**
     * Effectuer un dépôt sur un compte
     */
    public function effectuerDepot(array $data)
    {
        return DB::transaction(function () use ($data) {
            try {
                // Vérifier que le compte existe et est actif
                $compte = Compte::find($data['compte_id']);
                if (!$compte) {
                    throw new \Exception("Compte introuvable");
                }

                if ($compte->statut !== 'actif') {
                    throw new \Exception("Le compte n'est pas actif");
                }

                // Générer le numéro de transaction
                $numeroTransaction = $this->generateNumeroTransaction();

                // Créer la transaction
                $transaction = Transaction::create([
                    'numero' => $numeroTransaction,
                    'compte_id' => $data['compte_id'],
                    'type' => 'depot',
                    'montant' => $data['montant'],
                    'devise' => 'FCFA', // Devise par défaut
                    'statut' => 'complete',
                    'date_transaction' => now(),
                    'metadata' => $data['metadata'] ?? null,
                ]);

                // Sauvegarder dans MongoDB
                $this->saveToMongoDB($transaction);

                // Envoyer une notification au client
                $this->notifierClientDepot($compte, $transaction);

                Log::info("Dépôt effectué avec succès: {$transaction->numero} sur le compte {$compte->numero_compte}");

                return $transaction->load('compte');
            } catch (\Throwable $e) {
                Log::error("Erreur lors du dépôt: " . $e->getMessage());
                throw $e;
            }
        });
    }

    /**
     * Génère un numéro de transaction unique
     */
    private function generateNumeroTransaction(): string
    {
        do {
            $numero = 'T' . date('Ymd') . str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
        } while (Transaction::where('numero', $numero)->exists());

        return $numero;
    }

    /**
     * Notifier le client après un dépôt
     */
    private function notifierClientDepot(Compte $compte, Transaction $transaction): void
    {
        try {
            $client = $compte->client->user;

            $message = "Dépôt de " . number_format($transaction->montant, 0, ',', ' ') . " {$transaction->devise} effectué sur votre compte {$compte->numero_compte}.\n" .
                "Numéro de transaction: {$transaction->numero}\n" .
                "Date: " . $transaction->date_transaction->format('d/m/Y H:i');

            // Notification par email
            $this->notificationManager->send(
                $client->email,
                'Confirmation de dépôt',
                $message
            );

            // Notification par SMS
            $this->notificationManager->send(
                $client->telephone,
                null,
                "Dépôt confirmé: " . number_format($transaction->montant, 0, ',', ' ') . " {$transaction->devise} sur {$compte->numero_compte}"
            );
        } catch (\Throwable $e) {
            Log::error("Erreur lors de la notification du dépôt: " . $e->getMessage());
            // Ne pas bloquer la transaction si la notification échoue
        }
    }

    /**
     * Récupérer les transactions d'un compte
     */
    public function getTransactionsByCompte(string $compteId, array $filters = [], int $page = 1, int $limit = 10): LengthAwarePaginator
    {
        $query = Transaction::where('compte_id', $compteId)
            ->with('compte')
            ->orderBy('date_transaction', 'desc');

        // Appliquer les filtres
        if (isset($filters['type']) && in_array($filters['type'], ['depot', 'retrait'])) {
            $query->where('type', $filters['type']);
        }

        if (isset($filters['date_debut'])) {
            $query->where('date_transaction', '>=', $filters['date_debut']);
        }

        if (isset($filters['date_fin'])) {
            $query->where('date_transaction', '<=', $filters['date_fin']);
        }

        return $query->paginate($limit, ['*'], 'page', $page);
    }

    /**
     * Sauvegarder la transaction dans MongoDB
     */
    private function saveToMongoDB(Transaction $transaction): void
    {
        try {
            $mongodb = DB::connection('mongodb');
            $collection = $mongodb->getCollection('transactions');

            $data = [
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
                'created_at' => $transaction->created_at->toISOString(),
                'updated_at' => $transaction->updated_at->toISOString(),
            ];

            $collection->insertOne($data);

            Log::info("Transaction sauvegardée dans MongoDB: {$transaction->numero}");
        } catch (\Throwable $e) {
            Log::error("Erreur lors de la sauvegarde dans MongoDB: " . $e->getMessage());
            // Ne pas bloquer la transaction si la sauvegarde MongoDB échoue
        }
    }

    /**
     * Récupérer les statistiques des transactions d'un compte
     */
    public function getStatistiquesByCompte(string $compteId): array
    {
        $compte = Compte::find($compteId);
        if (!$compte) {
            throw new \Exception("Compte introuvable");
        }

        $totalDepot = $compte->transactions()->where('type', 'depot')->sum('montant');
        $totalRetrait = $compte->transactions()->where('type', 'retrait')->sum('montant');
        $nombreTransactions = $compte->transactions()->count();
        $derniereTransaction = $compte->transactions()->latest('date_transaction')->first();

        return [
            'total_depot' => (float) $totalDepot,
            'total_retrait' => (float) $totalRetrait,
            'nombre_transactions' => $nombreTransactions,
            'derniere_transaction' => $derniereTransaction,
        ];
    }

    /**
     * Retourne la liste des comptes selon le rôle de l’utilisateur.
     */
    public function getAllByUser(User $user, array $filters = [], int $page = 1, int $limit = 10): LengthAwarePaginator
    {
        // ✅ Admin : accès à tous les comptes
        if ($user->admin) {
            return $this->transactionRepository->all($filters, $page, $limit);
        }

        // ✅ Client : seulement ses comptes
        if ($user->client) {
            $filters['client_id'] = $user->client->id;
            return $this->transactionRepository->all($filters, $page, $limit);
        }

        // 🚫 Autres rôles : accès interdit
        abort(403, "Accès non autorisé");
    }
}
