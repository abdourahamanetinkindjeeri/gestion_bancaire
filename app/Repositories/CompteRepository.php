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

        // Filtrage par statut si spécifié, sinon comptes actifs par défaut
        if (!empty($filters['statut'])) {
            $query->where('statut', $filters['statut']);
        } else {
            $query->where('statut', 'actif');
        }

        // Filtrage par type si spécifié
        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        } else {
            $query->where(function ($q) {
                $q->where('type', 'cheque')
                    ->orWhere('type', 'epargne');
            });
        }

        // Filtrage par client_id si spécifié
        if (!empty($filters['client_id'])) {
            $query->where('client_id', $filters['client_id']);
        }

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
            ->when(!empty($filters['client_id']), function ($q) use ($filters) {
                $q->where('client_id', $filters['client_id']);
            })
            ->when(!empty($filters['search']), function ($q) use ($filters) {
                $q->where('numero_compte', 'like', "%{$filters['search']}%")
                  ->orWhere('type', 'like', "%{$filters['search']}%")
                  ->orWhere('devise', 'like', "%{$filters['search']}%");
            })
            ->orderBy($filters['sort'] ?? 'created_at', $filters['order'] ?? 'desc');

        return $query->paginate($limit, ['*'], 'page', $page);
    }

    /**
     * Trouver un compte par numéro ou ID
     */
    public function findByNumeroOrId(string $numeroOrId): ?Compte
    {
        // Vérifier si c'est un UUID valide
        $uuidPattern = '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i';

        if (preg_match($uuidPattern, $numeroOrId)) {
            // C'est un UUID, rechercher par ID dans PostgreSQL
            $compte = $this->model->where('id', $numeroOrId)->first();
            if ($compte) {
                return $compte;
            }
            // Si pas trouvé dans PostgreSQL, chercher dans Neon
            return $this->findInNeonById($numeroOrId);
        } else {
            // Ce n'est pas un UUID, rechercher par numéro de compte dans PostgreSQL
            $compte = $this->model->where('numero_compte', $numeroOrId)->first();
            if ($compte) {
                return $compte;
            }
            // Si pas trouvé dans PostgreSQL, chercher dans Neon
            return $this->findInNeonByNumero($numeroOrId);
        }
    }

    /**
     * Chercher un compte dans Neon par ID
     */
    private function findInNeonById(string $id): ?Compte
    {
        $result = DB::connection('neon')
            ->table('comptes_bloque')
            ->where('id', $id)
            ->first();

        if ($result) {
            // Convertir en modèle Compte
            return new Compte((array) $result);
        }

        return null;
    }

    /**
     * Chercher un compte dans Neon par numéro
     */
    private function findInNeonByNumero(string $numero): ?Compte
    {
        $result = DB::connection('neon')
            ->table('comptes_bloque')
            ->where('numero_compte', $numero)
            ->first();

        if ($result) {
            // Convertir en modèle Compte
            return new Compte((array) $result);
        }

        return null;
    }
}
