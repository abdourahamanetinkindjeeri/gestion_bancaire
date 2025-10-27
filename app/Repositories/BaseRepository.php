<?php

namespace App\Repositories;

use App\Repositories\Contracts\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BaseRepository implements BaseRepositoryInterface
{
    protected Model $model;

    protected array $searchable = [];
    protected array $sortable = [];

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * Récupère tous les enregistrements avec filtres, recherche et tri
     */
    public function all(array $filters = [], int $page = 1, int $limit = 10): LengthAwarePaginator
    {
        $query = $this->model->newQuery()
            ->filter($filters)
            ->search($filters['search'] ?? null, $this->searchable)
            ->sort($filters['sort'] ?? null, $filters['order'] ?? null);

        $limit = min($limit, 100);

        return $query->paginate($limit, ['*'], 'page', $page);
    }

    /**
     * Recherche locale + fallback cloud
     */
    public function find(int|string $id): ?Model
    {
        // Recherche locale
        $model = $this->model->find($id);

        if ($model) {
            return $model;
        }

        // Recherche cloud
        $cloudData = $this->findByIdFromCloud($id);
        if ($cloudData) {
            // Créer un objet Model à partir des données cloud
            return $this->model->newFromBuilder($cloudData);
        }

        return null;
    }

    /**
     * Recherche sur Neon (cloud)
     */
    public function findByIdFromCloud(int|string $id, string $table = null): ?object
    {
        try {
            $table = $table ?? $this->model->getTable();

            return DB::connection('neon')
                ->table($table)
                ->where('id', $id)
                ->first();
        } catch (\Exception $e) {
            // Log l'erreur mais ne pas interrompre l'exécution
            Log::warning("Erreur lors de la recherche cloud pour {$table}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Crée un enregistrement local
     */
    public function create(array $data): Model
    {
        return $this->model->create($data);
    }

    /**
     * Met à jour un enregistrement local
     */
    public function update(int|string $id, array $data): ?Model
    {
        $model = $this->model->find($id);

        if (!$model) return null;

        $model->update($data);

        return $model;
    }

    /**
     * Supprime un enregistrement local
     */
    public function delete(int|string $id): bool
    {
        $model = $this->model->find($id);

        return $model ? $model->delete() : false;
    }
}
