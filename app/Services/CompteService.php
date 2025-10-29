<?php

namespace App\Services;

use App\Events\CompteCreated;
use App\Jobs\ChangeCompteStatusJob;
use App\Models\Compte;
use App\Repositories\ClientRepository;
use App\Repositories\CompteRepository;
use App\Models\Client;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;


class CompteService extends BaseService
{
    protected ClientRepository $clientRepository;

    public function __construct(CompteRepository $repository, ClientRepository $clientRepository)
    {
        parent::__construct($repository);
        $this->clientRepository = $clientRepository;
    }

    public function getAll(array $filters = [], int $page = 1, int $limit = 10): LengthAwarePaginator
    {

        return $this->repository->all($filters, $page, $limit);
    }


    /**
     * Récupère tous les comptes actifs
     */
    public function getAllNonArchived(array $filters = [], int $page = 1, int $limit = 10)
    {
        return $this->repository->getAllNonArchived($filters, $page, $limit);
    }

    /**
     * Récupère tous les comptes archivés
     */
    public function getAllArchived(array $filters = [], int $page = 1, int $limit = 10)
    {
        return $this->repository->getAllArchived($filters, $page, $limit);
    }

    /**
     * Créer un nouveau compte avec client
     */
    public function createCompte(array $data)
    {
        return DB::transaction(function () use ($data) {
            try {
                // Vérifier si le client existe
                $client = $this->findOrCreateClient($data['client']);

                // Créer le compte
                $compte = $this->createCompteForClient($client, $data);

                // Déclencher l'événement
                event(new CompteCreated($compte));

                Log::info("Compte créé avec succès: {$compte->numero_compte} pour le client {$client->email}");

                return $compte;
            } catch (\Throwable $e) {
                Log::error("Erreur lors de la création du compte: " . $e->getMessage());
                throw $e;
            }
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

    /**
     * Mettre à jour un compte
     */
    public function updateCompte(string $compteId, array $data)
    {
        return DB::transaction(function () use ($compteId, $data) {
            try {
                $compte = $this->repository->find($compteId);

                if (!$compte) {
                    throw new \Exception("Compte introuvable");
                }

                // Vérifications de logique métier
                if (isset($data['statut']) && $data['statut'] === 'bloque' && $compte->statut !== 'bloque') {
                    throw new \Exception("Utilisez l'endpoint de blocage pour bloquer un compte");
                }

                if (isset($data['statut']) && $data['statut'] !== 'bloque' && $compte->statut === 'bloque') {
                    // Si on débloque le compte, on nettoie les dates de blocage
                    $data['debut_blocage'] = null;
                    $data['fin_blocage'] = null;
                    $data['metadata'] = array_merge($compte->metadata ?? [], [
                        'date_deblocage' => now()->toISOString(),
                    ]);
                }

                // Mettre à jour le compte
                $compte->update($data);

                Log::info("Compte mis à jour avec succès: {$compte->numero_compte}");

                return $compte;
            } catch (\Throwable $e) {
                Log::error("Erreur lors de la mise à jour du compte: " . $e->getMessage());
                throw $e;
            }
        });
    }

    // /**
    //  * Bloquer un compte (seulement les comptes épargne actifs)
    //  */
    // public function bloquerCompte(string $compteId, array $data)
    // {
    //     return DB::transaction(function () use ($compteId, $data) {
    //         try {
    //             $compte = $this->repository->find($compteId);

    //             if (!$compte) {
    //                 throw new \Exception("Compte introuvable");
    //             }

    //             // Vérifications métier
    //             if ($compte->type !== 'epargne') {
    //                 throw new \Exception("Seuls les comptes épargne peuvent être bloqués");
    //             }

    //             if ($compte->statut !== 'actif') {
    //                 throw new \Exception("Seul un compte actif peut être bloqué");
    //             }

    //             // Calculer la date de fin de blocage (toujours en mois)
    //             $debutBlocage = now();
    //             $duree = $data['duree'];
    //             $finBlocage = $debutBlocage->copy()->addMonths($duree);

    //             // Mettre à jour le compte
    //             $compte->update([
    //                 'statut' => 'bloque',
    //                 'debut_blocage' => $debutBlocage,
    //                 'fin_blocage' => $finBlocage,
    //                 'metadata' => array_merge($compte->metadata ?? [], [
    //                     'motif_blocage' => $data['motif'],
    //                     'duree_blocage_mois' => $duree,
    //                     'date_debut_blocage' => $debutBlocage->toISOString(),
    //                     'date_fin_blocage_prevue' => $finBlocage->toISOString(),
    //                 ])
    //             ]);

    //             Log::info("Compte épargne bloqué avec succès: {$compte->numero_compte} pour {$duree} mois");

    //             return $compte;
    //         } catch (\Throwable $e) {
    //             Log::error("Erreur lors du blocage du compte: " . $e->getMessage());
    //             throw $e;
    //         }
    //     });
    // }

    public function bloquerCompte(string $compteId, array $data)
    {
        return DB::transaction(function () use ($compteId, $data) {
            try {
                $compte = $this->repository->find($compteId);

                if (!$compte) {
                    throw new \Exception("Compte introuvable");
                }

                if ($compte->type !== 'epargne') {
                    throw new \Exception("Seuls les comptes épargne peuvent être bloqués");
                }

                if ($compte->statut !== 'actif') {
                    throw new \Exception("Seul un compte actif peut être bloqué");
                }

                // Prendre la date de début renseignée ou maintenant
                $debutBlocage = !empty($data['debut_blocage'])
                    ? \Carbon\Carbon::parse($data['debut_blocage'])
                    : now();

                $duree = $data['duree'];
                $unite = $data['unite'] ?? 'mois'; // par défaut 'mois'

                // Calculer la date de fin selon l'unité
                if ($unite === 'jour') {
                    $finBlocage = $debutBlocage->copy()->addDays($duree);
                } else {
                    $finBlocage = $debutBlocage->copy()->addMonths($duree);
                }

                // Mettre à jour le compte
                $compte->update([
                    'debut_blocage' => $debutBlocage,
                    'fin_blocage' => $finBlocage,
                    'metadata' => array_merge($compte->metadata ?? [], [
                        'motif_blocage' => $data['motif'],
                        'duree_blocage' => $duree,
                        'unite_blocage' => $unite,
                        'date_debut_blocage' => $debutBlocage->toISOString(),
                        'date_fin_blocage_prevue' => $finBlocage->toISOString(),
                    ])
                ]);

                // Job pour changer le statut à 'bloque' à la date de début
                $secondsUntilStart = max(0, $debutBlocage->diffInSeconds(now()));
                ChangeCompteStatusJob::dispatch($compte->id, 'bloque')
                    ->delay($secondsUntilStart);

                // Job pour remettre le compte à 'actif' à la fin du blocage
                $secondsUntilEnd = max(0, $finBlocage->diffInSeconds(now()));
                ChangeCompteStatusJob::dispatch($compte->id, 'actif')
                    ->delay($secondsUntilEnd);

                Log::info("Blocage du compte planifié pour {$compte->numero_compte} de {$debutBlocage} à {$finBlocage}");

                return $compte;
            } catch (\Throwable $e) {
                Log::error("Erreur lors du blocage du compte: " . $e->getMessage());
                throw $e;
            }
        });
    }





    /**
     * Débloquer un compte manuellement (sur demande du client)
     */
    public function debloquerCompteManuellement(string $compteId)
    {
        return DB::transaction(function () use ($compteId) {
            try {
                $compte = $this->repository->find($compteId);

                if (!$compte) {
                    throw new \Exception("Compte introuvable");
                }

                if ($compte->statut !== 'bloque') {
                    throw new \Exception("Le compte n'est pas bloqué");
                }

                // Mettre à jour le compte
                $compte->update([
                    'statut' => 'actif',
                    'debut_blocage' => null,
                    'fin_blocage' => null,
                    'metadata' => array_merge($compte->metadata ?? [], [
                        'date_deblocage_manuel' => now()->toISOString(),
                        'motif_deblocage' => 'Demande client',
                    ])
                ]);

                Log::info("Compte débloqué manuellement avec succès: {$compte->numero_compte}");

                return $compte;
            } catch (\Throwable $e) {
                Log::error("Erreur lors du déblocage manuel du compte: " . $e->getMessage());
                throw $e;
            }
        });
    }

    /**
     * Supprimer un compte (soft delete)
     */
    public function deleteCompte(string $compteId)
    {
        return DB::transaction(function () use ($compteId) {
            try {
                $compte = $this->repository->find($compteId);

                if (!$compte) {
                    throw new \Exception("Compte introuvable");
                }

                // Vérifications métier avant suppression
                if ($compte->statut === 'bloque') {
                    throw new \Exception("Impossible de supprimer un compte bloqué. Débloquez-le d'abord.");
                }

                if ($compte->solde > 0) {
                    throw new \Exception("Impossible de supprimer un compte avec un solde positif. Effectuez un retrait préalable.");
                }

                // Soft delete du compte
                $compte->delete();

                Log::info("Compte supprimé avec succès (soft delete): {$compte->numero_compte}");

                return $compte;
            } catch (\Throwable $e) {
                Log::error("Erreur lors de la suppression du compte: " . $e->getMessage());
                throw $e;
            }
        });
    }
}
