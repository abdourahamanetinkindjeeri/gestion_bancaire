<?php

namespace App\Repositories;

use App\Models\Compte;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class CompteRepository extends BaseRepository
{
    protected array $searchable = ['numero_compte', 'type', 'devise'];
    protected array $sortable = ['numero_compte', 'type', 'solde_initial', 'statut', 'created_at', 'updated_at'];

    public function __construct(Compte $model)
    {
        parent::__construct($model);
    }

    /**
     * Récupère les comptes actifs de type épargne ou chèque
     */
    public function all(array $filters = [], int $page = 1, int $limit = 10): LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        $query->where(function ($q) {
            $q->where('type', 'cheque')
                ->orWhere('type', 'epargne');
        })->where('statut', 'actif');

        // Recherches globales
        if (!empty($filters['search'])) {
            $query->search($filters['search'], $this->searchable);
        }

        // Tri
        if (!empty($filters['sort'])) {
            $query->sort($filters['sort'], $filters['order'] ?? 'asc');
        }

        // Limite maximum
        $limit = min($limit, 100);

        return $query->paginate($limit, ['*'], 'page', $page);
    }



    /**
     * Récupère tous les comptes actifs (PostgreSQL)
     */
    public function getAllNonArchived(array $filters = [], int $page = 1, int $limit = 10): LengthAwarePaginator
    {
        $filters['statut'] = 'actif'; // filtrer les comptes actifs
        return $this->all($filters, $page, $limit); // utilise la table locale
    }

    /**
     * Récupère tous les comptes archivés (Neon)
     */
    public function getAllArchived(array $filters = [], int $page = 1, int $limit = 10): LengthAwarePaginator
    {
        $filters['statut'] = 'bloque';
        $limit = min($limit, 100);

        $query = DB::connection('neon')
            ->table('comptes_bloque')
            ->when(!empty($filters['search']), function ($q) use ($filters) {
                $q->where('numero_compte', 'like', "%{$filters['search']}%")
                    ->orWhere('type', 'like', "%{$filters['search']}%")
                    ->orWhere('devise', 'like', "%{$filters['search']}%");
            })
            ->orderBy($filters['sort'] ?? 'created_at', $filters['order'] ?? 'desc');

        return $query->paginate($limit, ['*'], 'page', $page);
    }
}
