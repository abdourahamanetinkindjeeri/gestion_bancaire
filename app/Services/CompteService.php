<?php

namespace App\Services;

use App\Events\CompteCreated;
use App\Models\Compte;
use App\Repositories\ClientRepository;
use App\Repositories\CompteRepository;
use App\Models\Client;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;


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

    /**
     * Créer un nouveau compte avec client
     */
    public function createCompte(array $data)
    {
        return DB::transaction(function () use ($data) {
            // Vérifier si le client existe
            $client = $this->findOrCreateClient($data['client']);

            // Créer le compte
            $compte = $this->createCompteForClient($client, $data);

            // Déclencher l'événement
            event(new CompteCreated($compte));

            return $compte;
        });
    }

    private function findOrCreateClient(array $clientData): Client
    {
        // Chercher le client par email ou téléphone
        $client = Client::where('email', $clientData['email'])
            ->orWhere('telephone', $clientData['telephone'])
            ->first();

        if ($client) {
            return $client;
        }

        // Créer un nouveau client
        return Client::create([
            'code_client' => 'CLT' . Str::random(8),
            'titulaire' => $clientData['titulaire'],
            'nci' => $clientData['nci'],
            'email' => $clientData['email'],
            'telephone' => $clientData['telephone'],
            'adresse' => $clientData['adresse'],
            'actif' => true,
        ]);
    }


    private function createCompteForClient(Client $client, array $data): Compte
    {
        $numeroCompte = $this->generateNumeroCompte();

        return Compte::create([
            'numero_compte' => $numeroCompte,
            'type' => $data['type'],
            'solde_initial' => $data['soldeInitial'],
            'devise' => $data['devise'],
            'statut' => 'actif',
            'client_id' => $client->id,
        ]);
    }

    private function generateNumeroCompte(): string
    {
        do {
            $numero = 'C00' . str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
        } while (Compte::where('numero_compte', $numero)->exists());

        return $numero;
    }



}

