<?php

namespace App\Http\Controllers;

use App\Http\Requests\CompteStoreRequest;
use App\Http\Requests\CompteBloquerRequest;
use App\Http\Requests\CompteUpdateRequest;
use App\Http\Resources\CompteResource;
use App\Http\Resources\CompteResourceCollection;
use App\Services\ClientService;
use App\Services\CompteService;
use Illuminate\Http\Request;
use App\Traits\ApiResponser;
use Symfony\Component\HttpFoundation\Response;

/**
 * @OA\Info(
 *     title="API de Gestion des Comptes",
 *     version="1.0.0",
 *     description="API pour la gestion des comptes bancaires"
 * )
 *
 * @OA\Server(
 * url="https://tinkin-jeeri-chue.onrender.com/tinkin",
 * description="Serveur de production"
 * )
 * @OA\Server(
 * url="http://localhost:8000/tinkin",
 * description="Serveur local"
 * )
 *
 * @OA\Tag(
 *     name="Comptes",
 *     description="Gestion des comptes bancaires et de leurs transactions"
 * )
 */
class CompteController extends Controller
{
    use ApiResponser;

    protected CompteService $compteService;
    protected ClientService $clientService;

    public function __construct(CompteService $compteService, ClientService $clientService)
    {
        $this->compteService = $compteService;
        $this->clientService = $clientService;
    }

    /**
     * Liste paginée des comptes
     *
     * @OA\Get(
     *     path="/v1/comptes",
     *     tags={"Comptes"},
     *     summary="Liste paginée des comptes",
     *     description="Récupère une liste paginée de tous les comptes avec possibilité de filtrage",
     *     @OA\Parameter(name="page", in="query", @OA\Schema(type="integer", default=1)),
     *     @OA\Parameter(name="limit", in="query", @OA\Schema(type="integer", default=10)),
     *     @OA\Parameter(name="type", in="query", @OA\Schema(type="string", enum={"cheque", "epargne"})),
     *     @OA\Parameter(name="statut", in="query", @OA\Schema(type="string")),
     *     @OA\Parameter(name="search", in="query", @OA\Schema(type="string")),
     *     @OA\Parameter(name="sort", in="query", @OA\Schema(type="string", default="id")),
     *     @OA\Parameter(name="order", in="query", @OA\Schema(type="string", enum={"asc", "desc"}, default="desc")),
     *     @OA\Response(
     *         response=200,
     *         description="Liste des comptes récupérée avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="http_code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Liste des comptes récupérée avec succès"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/Compte")
     *             ),
     *             @OA\Property(property="pagination", ref="#/components/schemas/Pagination")
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $filters = $request->all();
        $page = (int) $request->get('page', 1);
        $limit = (int) $request->get('limit', 10);

        $comptes = $this->compteService->getAll($filters, $page, $limit);

        return $this->successResponse(
            $comptes,
            "Liste des comptes récupérée avec succès"
        );
    }

    /**
     * Liste des comptes non archivés
     *
     * @OA\Get(
     *     path="/v1/comptes/archives",
     *     tags={"Comptes"},
     *     summary="Liste des comptes  archivés",
     *     description="Récupère tous les comptes archivés avec possibilité de filtrage",
     *     @OA\Parameter(name="page", in="query", @OA\Schema(type="integer", default=1)),
     *     @OA\Parameter(name="limit", in="query", @OA\Schema(type="integer", default=10)),
     *     @OA\Parameter(name="type", in="query", @OA\Schema(type="string", enum={"cheque", "epargne", "courant"})),
     *     @OA\Parameter(name="statut", in="query", @OA\Schema(type="string")),
     *     @OA\Parameter(name="search", in="query", @OA\Schema(type="string")),
     *     @OA\Parameter(name="sort", in="query", @OA\Schema(type="string", default="id")),
     *     @OA\Parameter(name="order", in="query", @OA\Schema(type="string", enum={"asc","desc"}, default="desc")),
     *     @OA\Response(
     *         response=200,
     *         description="Liste des comptes  archivés récupérée avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="http_code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Liste des comptes non archives récupérée avec succès"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/Compte")
     *             ),
     *             @OA\Property(property="pagination", ref="#/components/schemas/Pagination")
     *         )
     *     )
     * )
     */
    // public function getComptesNotArchived(Request $request)
    // {
    //     $filters = $request->all();
    //     $page = (int) $request->get('page', 1);
    //     $limit = (int) $request->get('limit', 10);

    //     $comptes = $this->compteService->getAllNonArchived($filters, $page, $limit);

    //     return $this->successResponse(
    //         $comptes,
    //         "Liste des comptes non archivés récupérée avec succès"
    //     );
    // }

    public function getComptesAllArchived(Request $request)
    {
        $filters = $request->all();
        $page = (int) $request->get('page', 1);
        $limit = (int) $request->get('limit', 10);

        $comptes = $this->compteService->getAllArchived($filters, $page, $limit);

        return $this->successResponse(
            $comptes,
            "Liste des comptes archivés récupérée avec succès"
        );
    }

    /**
     * @OA\Post(
     *     path="/v1/comptes",
     *     tags={"Comptes"},
     *     summary="Créer un nouveau compte",
     *     description="Crée un nouveau compte bancaire avec un client. Si le client n'existe pas, il est créé automatiquement.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"type","soldeInitial","devise","client"},
     *             @OA\Property(property="type", type="string", enum={"cheque", "epargne"}, example="cheque"),
     *             @OA\Property(property="soldeInitial", type="number", minimum=10000, example=50000),
     *             @OA\Property(property="devise", type="string", example="FCFA"),
     *             @OA\Property(property="client", type="object",
     *                 @OA\Property(property="titulaire", type="string", example="Festo BA"),
     *                 @OA\Property(property="nci", type="string", example="1234567890987", description="Numéro NCI de 13 chiffres commençant par 1 ou 2"),
     *                 @OA\Property(property="telephone", type="string", example="781465554", description="Numéro téléphone sénégalais"),
     *                 @OA\Property(property="email", type="string", format="email", example="festobah@gmail.com"),
     *                 @OA\Property(property="adresse", type="string", example="Dakar, Sénégal")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Compte créé avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="http_code", type="integer", example=201),
     *             @OA\Property(property="message", type="string", example="Compte créé avec succès"),
     *             @OA\Property(property="data", type="object"),
     *             @OA\Property(property="metadata", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erreur de validation",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="http_code", type="integer", example=422),
     *             @OA\Property(property="message", type="string", example="Les données fournies ne sont pas valides"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function store(CompteStoreRequest $request)
    {
        try {
            $validatedData = $request->validated();

            $compte = $this->compteService->createCompte($validatedData);

            return $this->successResponse(
                $compte->load('client'),
                "Compte créé avec succès",
                Response::HTTP_CREATED
            );
        } catch (\Throwable $e) {
            return $this->errorResponse(
                "Erreur lors de la création du compte",
                422
            );
        }
    }

    /**
     * @OA\Get(
     *     path="/v1/comptes/{id}",
     *     tags={"Comptes"},
     *     summary="Affiche un compte spécifique",
     *     description="Récupère les détails d'un compte par son ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID du compte",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Compte récupéré avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="http_code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Compte récupéré avec succès"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Compte introuvable",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="http_code", type="integer", example=404),
     *             @OA\Property(property="message", type="string", example="Compte introuvable")
     *         )
     *     )
     * )
     */
    public function show(int|string $id)
    {
        $compte = $this->compteService->getById($id);

        if (!$compte) {
            return $this->errorResponse("Compte introuvable", 404);
        }

        return $this->successResponse(
            new CompteResource($compte, $this->clientService),
            "Compte récupéré avec succès"
        );
    }

    /**
     * @OA\Put(
     *     path="/v1/comptes/{id}",
     *     tags={"Comptes"},
     *     summary="Mettre à jour un compte",
     *     description="Met à jour les informations d'un compte bancaire existant",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID du compte à mettre à jour",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="type", type="string", enum={"cheque", "epargne", "courant"}, example="epargne"),
     *             @OA\Property(property="solde_initial", type="number", minimum=10000, example=50000),
     *             @OA\Property(property="devise", type="string", example="FCFA"),
     *             @OA\Property(property="statut", type="string", enum={"actif", "inactif", "bloque", "ferme"}, example="actif"),
     *             @OA\Property(property="metadata", type="object", example={"notes": "Mise à jour du solde"})
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Compte mis à jour avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="http_code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Compte mis à jour avec succès"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Compte introuvable",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="http_code", type="integer", example=404),
     *             @OA\Property(property="message", type="string", example="Compte introuvable")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erreur de validation",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="http_code", type="integer", example=422),
     *             @OA\Property(property="message", type="string", example="Les données fournies ne sont pas valides"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function update(CompteUpdateRequest $request, int|string $id)
    {
        try {
            $validatedData = $request->validated();

            $compte = $this->compteService->updateCompte($id, $validatedData);

            return $this->successResponse(
                new CompteResource($compte, $this->clientService),
                "Compte mis à jour avec succès"
            );
        } catch (\Throwable $e) {
            return $this->errorResponse(
                $e->getMessage(),
                422
            );
        }
    }

    /**
     * @OA\Post(
     *     path="/v1/comptes/{id}/bloquer",
     *     tags={"Comptes"},
     *     summary="Bloquer un compte épargne",
     *     description="Bloque un compte épargne actif pour une durée déterminée avec un motif spécifique. L'unité peut être en mois ou en jours. La date de début peut être renseignée, sinon elle sera définie automatiquement.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID du compte épargne à bloquer",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"motif","duree","unite"},
     *             @OA\Property(property="motif", type="string", example="Activité suspecte détectée"),
     *             @OA\Property(property="duree", type="integer", minimum=1, example=3),
     *             @OA\Property(property="unite", type="string", enum={"mois","jour"}, example="mois"),
     *             @OA\Property(
     *                 property="debut_blocage",
     *                 type="string",
     *                 format="date-time",
     *                 example="2025-10-30T08:00:00Z",
     *                 description="Date et heure du début du blocage (optionnel). Si non renseignée, la date actuelle sera utilisée."
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Compte bloqué avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="http_code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Compte bloqué avec succès"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Compte introuvable",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="http_code", type="integer", example=404),
     *             @OA\Property(property="message", type="string", example="Compte introuvable")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erreur de validation ou contraintes métier non respectées",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="http_code", type="integer", example=422),
     *             @OA\Property(property="message", type="string", example="Seuls les comptes épargne actifs peuvent être bloqués"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */

    public function bloquer(CompteBloquerRequest $request, int|string $id)
    {
        try {
            $validatedData = $request->validated();

            // Si la date de début n'est pas fournie, utiliser maintenant
            if (!isset($validatedData['debut_blocage']) || empty($validatedData['debut_blocage'])) {
                $validatedData['debut_blocage'] = now();
            }

            // Appel au service pour bloquer le compte
            $compte = $this->compteService->bloquerCompte($id, $validatedData);

            return $this->successResponse(
                new CompteResource($compte, $this->clientService),
                "Compte bloqué avec succès"
            );
        } catch (\Throwable $e) {
            return $this->errorResponse(
                $e->getMessage(),
                422
            );
        }
    }



    // public function debloquer(int|string $id)
    // {
    //     try {
    //         $compte = $this->compteService->debloquerCompteManuellement($id);

    //         return $this->successResponse(
    //             new CompteResource($compte, $this->clientService),
    //             "Compte débloqué avec succès"
    //         );
    //     } catch (\Throwable $e) {
    //         return $this->errorResponse(
    //             $e->getMessage(),
    //             422
    //         );
    //     }
    // }


    // public function desarchiver(int|string $id)
    // {
    //     try {
    //         // Dispatch du job pour désarchiver le compte
    //         \App\Jobs\DesarchiverCompteJob::dispatch($id);

    //         return $this->successResponse(
    //             null,
    //             "Demande de désarchivage du compte en cours"
    //         );
    //     } catch (\Throwable $e) {
    //         return $this->errorResponse(
    //             "Erreur lors de la demande de désarchivage: " . $e->getMessage(),
    //             422
    //         );
    //     }
    // }

    /**
     * @OA\Delete(
     *     path="/v1/comptes/{id}",
     *     tags={"Comptes"},
     *     summary="Supprimer un compte (soft delete)",
     *     description="Supprime un compte bancaire de manière logicielle. Le compte doit être actif et avoir un solde nul.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID du compte à supprimer",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Compte supprimé avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="http_code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Compte supprimé avec succès"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Compte introuvable",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="http_code", type="integer", example=404),
     *             @OA\Property(property="message", type="string", example="Compte introuvable")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erreur de validation métier",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="http_code", type="integer", example=422),
     *             @OA\Property(property="message", type="string", example="Impossible de supprimer un compte bloqué. Débloquez-le d'abord.")
     *         )
     *     )
     * )
     */
    public function destroy(int|string $id)
    {
        try {
            $compte = $this->compteService->deleteCompte($id);

            return $this->successResponse(
                new CompteResource($compte, $this->clientService),
                "Compte supprimé avec succès"
            );
        } catch (\Throwable $e) {
            return $this->errorResponse(
                $e->getMessage(),
                422
            );
        }
    }

    /**
     * @OA\Get(
     *     path="/v1/comptes/{id}/details",
     *     tags={"Comptes"},
     *     summary="Détails d'un compte par numéro ou ID",
     *     description="Récupère les détails d'un compte bancaire par son numéro de compte ou son ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Numéro de compte ou ID du compte",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Détails du compte récupérés avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="http_code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Détails du compte récupérés avec succès"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Compte introuvable",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="http_code", type="integer", example=404),
     *             @OA\Property(property="message", type="string", example="Compte introuvable")
     *         )
     *     )
     * )
     */
    public function showDetails(string $numeroOrId)
    {
        $compte = $this->compteService->getByNumeroOrId($numeroOrId);

        if (!$compte) {
            return $this->errorResponse("Compte introuvable", 404);
        }

        return $this->successResponse(
            new CompteResource($compte, $this->clientService),
            "Détails du compte récupérés avec succès"
        );
    }
}
