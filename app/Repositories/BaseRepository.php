<?php

namespace App\Repositories;

use App\Repositories\Contracts\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

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
            ->when(!empty($filters), function ($q) use ($filters) {
                $q->filter($filters);
            })
            ->when(!empty($filters['search']), function ($q) use ($filters) {
                $q->search($filters['search'], $this->searchable);
            })
            ->when(!empty($filters['sort']), function ($q) use ($filters) {
                $q->sort($filters['sort'], $filters['order'] ?? 'asc');
            });

        $limit = min($limit, 100);

        return $query->paginate($limit, ['*'], 'page', $page);
    }

    /**
     * Recherche locale + fallback cloud avec cache
     */
    public function find(int|string $id, array $columns = ['*']): ?Model
    {
        $cacheKey = "cloud_{$this->model->getTable()}_{$id}";

        // 1️⃣ Recherche locale
        $model = $this->model->find($id, $columns);
        if ($model) {
            return $model;
        }

        // 2️⃣ Recherche dans le cloud avec cache
        $cloudData = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($id) {
            return $this->findByIdFromCloud($id);
        });

        if ($cloudData) {
            return $this->model->newFromBuilder((array) $cloudData);
        }

        return null;
    }

    /**
     * Recherche sur Neon (cloud)
     */
    public function findByIdFromCloud(int|string $id, ?string $table = null): ?object
    {
        try {
            $table = $table ?? $this->model->getTable() . '_bloque';

            // dd($table);
            return DB::connection('neon')
                ->table($table)
                ->where('id', $id)
                ->first();
        } catch (\Exception $e) {
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

    /**
     * Récupère tous les enregistrements non archivés
     */
    public function getAllNonArchived(array $filters = [], int $page = 1, int $limit = 10): LengthAwarePaginator
    {
        throw new \Exception("Méthode non implémentée dans la classe de base");
    }

    /**
     * Récupère tous les enregistrements archivés
     */
    public function getAllArchived(array $filters = [], int $page = 1, int $limit = 10): LengthAwarePaginator
    {
        throw new \Exception("Méthode non implémentée dans la classe de base");
    }
}
