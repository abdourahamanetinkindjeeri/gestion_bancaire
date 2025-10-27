<?php

namespace App\Services;

use App\Repositories\Contracts\BaseRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;

/**
 * Classe de service générique pour centraliser la logique commune
 * à toutes les entités manipulées par un repository.
 */
abstract class BaseService
{
    protected BaseRepositoryInterface $repository;

    public function __construct(BaseRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Récupère toutes les ressources avec pagination, filtres et tri.
     */
    public function getAll(array $filters = [], int $page = 1, int $limit = 10): LengthAwarePaginator
    {
        return $this->repository->all($filters, $page, $limit);
    }

    
}
