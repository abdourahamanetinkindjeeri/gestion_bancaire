<?php

namespace App\Services;

use App\Repositories\ClientRepository;
use App\Repositories\CompteRepository;


class CompteService extends BaseService
{
    protected ClientRepository $clientRepository;

    public function __construct(CompteRepository $repository, ClientRepository $clientRepository)
    {
        parent::__construct($repository);
        $this->clientRepository = $clientRepository;
    }

    /**
     * Récupère tous les comptes non archivés
     *
     * @param array $filters
     * @param int $page
     * @param int $limit
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getAllNonArchived(array $filters = [], int $page = 1, int $limit = 10)
    {
        // On s'assure que les comptes archivés ne sont pas retournés
        $filters['statut'] = "actif"; // ou ['deleted_at' => null] selon ton modèle

        return $this->repository->all($filters, $page, $limit);
    }

    /**
     * Récupère tous les comptes archivés
     */
    public function getAllArchived(array $filters = [], int $page = 1, int $limit = 10)
    {
        $filters['status'] = 'bloque'; // ou ['deleted_at' => 'not null'] selon ton modèle

        return $this->repository->all($filters, $page, $limit);
    }
}

