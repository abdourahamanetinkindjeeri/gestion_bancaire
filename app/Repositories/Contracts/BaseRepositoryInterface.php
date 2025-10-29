<?php

namespace App\Repositories\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;

interface BaseRepositoryInterface
{
    public function all(array $filters = [], int $page = 1, int $limit = 10): LengthAwarePaginator;
    public function find(int|string $id): ?Model;
    public function create(array $data): Model;
    public function update(int|string $id, array $data): ?Model;
    public function delete(int|string $id): bool;
    public function getAllNonArchived(array $filters = [], int $page = 1, int $limit = 10): LengthAwarePaginator;
    public function getAllArchived(array $filters = [], int $page = 1, int $limit = 10): LengthAwarePaginator;
    public function findByNumeroOrId(string $numeroOrId): ?Model;

}
