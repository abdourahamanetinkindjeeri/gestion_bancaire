<?php

namespace App\Repositories;

use App\Models\Transaction;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TransactionRepository extends BaseRepository
{
    protected array $searchable = ['numero', 'type', 'devise'];
    protected array $sortable = ['numero', 'type', 'montant', 'statut', 'date_transaction', 'created_at', 'updated_at'];

    public function __construct(Transaction $model)
    {
        parent::__construct($model);
    }

    /**
     * Récupère toutes les transactions avec filtres
     */
    public function all(array $filters = [], int $page = 1, int $limit = 10): LengthAwarePaginator
    {
        $query = $this->model->newQuery()->with('compte');

        // Filtrage par client_id si spécifié (pour les clients)
        if (!empty($filters['client_id'])) {
            $query->whereHas('compte', function ($q) use ($filters) {
                $q->where('client_id', $filters['client_id']);
            });
        }

        // Filtrage par type
        if (!empty($filters['type']) && in_array($filters['type'], ['depot', 'retrait'])) {
            $query->where('type', $filters['type']);
        }

        // Filtrage par statut
        if (!empty($filters['statut'])) {
            $query->where('statut', $filters['statut']);
        }

        // Filtrage par devise
        if (!empty($filters['devise'])) {
            $query->where('devise', $filters['devise']);
        }

        // Filtrage par date de début
        if (!empty($filters['date_debut'])) {
            $query->where('date_transaction', '>=', $filters['date_debut']);
        }

        // Filtrage par date de fin
        if (!empty($filters['date_fin'])) {
            $query->where('date_transaction', '<=', $filters['date_fin']);
        }

        // Recherche globale
        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('numero', 'like', "%{$filters['search']}%")
                  ->orWhere('type', 'like', "%{$filters['search']}%")
                  ->orWhere('devise', 'like', "%{$filters['search']}%")
                  ->orWhereHas('compte', function ($subQ) use ($filters) {
                      $subQ->where('numero_compte', 'like', "%{$filters['search']}%");
                  });
            });
        }

        // Tri
        if (!empty($filters['sort'])) {
            $query->orderBy($filters['sort'], $filters['order'] ?? 'desc');
        } else {
            $query->orderBy('date_transaction', 'desc');
        }

        // Limite maximum
        $limit = min($limit, 100);

        return $query->paginate($limit, ['*'], 'page', $page);
    }
}
