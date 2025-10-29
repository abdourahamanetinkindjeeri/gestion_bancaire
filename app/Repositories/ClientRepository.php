<?php

namespace App\Repositories;

use App\Models\Client;
use App\Repositories\Contracts\BaseRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ClientRepository implements BaseRepositoryInterface
{
    protected Client $model;

    public function __construct(Client $model)
    {
        $this->model = $model;
    }

    public function all(array $filters = [], int $page = 1, int $limit = 10): LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        // Appliquer les filtres
        foreach ($filters as $key => $value) {
            if ($value !== null && $value !== '') {
                $query->where($key, $value);
            }
        }

        return $query->paginate($limit, ['*'], 'page', $page);
    }

    public function find(int|string $id): ?Client
    {
        return $this->model->find($id);
    }

    public function findBy(array $criteria)
    {
        $query = $this->model->newQuery();

        foreach ($criteria as $key => $value) {
            $query->where($key, $value);
        }

        return $query->first();
    }

    public function create(array $data): Client
    {
        return $this->model->create($data);
    }

    public function update(int|string $id, array $data): ?Client
    {
        $model = $this->find($id);
        if ($model) {
            $model->update($data);
            return $model;
        }
        return null;
    }

    public function delete(int|string $id): bool
    {
        $model = $this->find($id);
        if ($model) {
            return $model->delete();
        }
        return false;
    }

    public function findByEmailOrTelephone(string $email, string $telephone)
    {
        return $this->model->where('email', $email)
            ->orWhere('telephone', $telephone)
            ->first();
    }

    public function getAllNonArchived(array $filters = [], int $page = 1, int $limit = 10): LengthAwarePaginator
    {
        // Pour les clients, on considère tous comme non archivés (pas de concept d'archivage)
        return $this->all($filters, $page, $limit);
    }

    public function getAllArchived(array $filters = [], int $page = 1, int $limit = 10): LengthAwarePaginator
    {
        // Pour les clients, pas d'archivage, retourner une collection vide
        return $this->model->newQuery()->whereRaw('1 = 0')->paginate($limit, ['*'], 'page', $page);
    }

    public function findByNumeroOrId(string $numeroOrId): ?Client
    {
        // Vérifier si la valeur ressemble à un UUID (format standard)
        $uuidPattern = '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i';

        $query = $this->model->newQuery();

        if (preg_match($uuidPattern, $numeroOrId)) {
            // Si c'est un UUID, rechercher dans l'ID
            $query->where('id', $numeroOrId);
        } else {
            // Sinon, rechercher dans les autres champs
            $query->where(function ($q) use ($numeroOrId) {
                $q->where('telephone', $numeroOrId)
                  ->orWhere('email', $numeroOrId)
                  ->orWhere('nci', $numeroOrId);
            });
        }

        return $query->first();
    }
}
