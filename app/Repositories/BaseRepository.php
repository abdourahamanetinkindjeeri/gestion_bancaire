<?php

namespace App\Repositories;

use App\Repositories\Contracts\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class BaseRepository implements BaseRepositoryInterface
{
    protected Model $model;

    protected array $searchable = [];
    protected array $sortable = [];

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function all(array $filters = [], int $page = 1, int $limit = 10): LengthAwarePaginator
    {
        $query = $this->model->newQuery()
            ->filter($filters)
            ->search($filters['search'] ?? null, $this->searchable)
            ->sort($filters['sort'] ?? null, $filters['order'] ?? null);

        $limit = min($limit, 100);
        return $query->paginate($limit, ['*'], 'page', $page);
    }

    public function find(int|string $id): ?Model
    {
        return $this->model->find($id);
    }

    public function create(array $data): Model
    {
        return $this->model->create($data);
    }

    public function update(int|string $id, array $data): ?Model
    {
        $model = $this->find($id);
        if (!$model) return null;
        $model->update($data);
        return $model;
    }

    public function delete(int|string $id): bool
    {
        $model = $this->find($id);
        return $model ? $model->delete() : false;
    }


}
