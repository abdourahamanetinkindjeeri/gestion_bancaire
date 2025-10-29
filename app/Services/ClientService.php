<?php

namespace App\Services;

use App\Events\CompteCreated;
use App\Models\Compte;
use App\Repositories\ClientRepository;
use App\Repositories\CompteRepository;
use App\Models\Client;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;


class ClientService extends BaseService
{
    protected ClientRepository $clientRepository;

    public function __construct(ClientRepository $clientRepository)
    {
        parent::__construct($clientRepository);
        $this->clientRepository = $clientRepository;
    }

    /**
     * Recherche un client par ID avec fallback cloud
     */
    public function getById(int|string $id): ?Client
    {
        return $this->repository->find($id);
    }

    /**
     * Recherche un client dans le cloud
     */
    public function getByIdFromCloud(int|string $id): ?Client
    {
        try {
            $cloudData = DB::connection('neon')
                ->table('clients')
                ->where('id', $id)
                ->first();
            if ($cloudData) {
                return Client::newFromBuilder($cloudData);
            }
        } catch (\Exception $e) {
            Log::warning("Erreur lors de la recherche cloud pour client {$id}: " . $e->getMessage());
        }
        return null;
    }

    /**
     * Recherche un client par numéro de téléphone, email, NCI ou ID
     */
    public function findByNumeroOrId(string $numeroOrId): ?Client
    {
        return $this->clientRepository->findByNumeroOrId($numeroOrId);
    }

}

