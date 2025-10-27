<?php

namespace App\Http\Controllers;

use App\Http\Requests\CompteStoreRequest;
use App\Http\Resources\CompteResource;
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
 * url="https://jeeri.onrender.com/tinkin",
 * description="Serveur de production"
 * )
 * @OA\Server(
 * url="http://localhost:8000/tinkin",
 * description="Serveur local"
 * )
 *
 * @OA\Tag(
 *     name="Comptes",
 *     description="Gestion des comptes bancaires"
 * )
 */
class CompteController extends Controller
{
    use ApiResponser;

    protected CompteService $compteService;

    public function __construct(CompteService $compteService)
    {
        $this->compteService = $compteService;
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
     *     @OA\Parameter(name="type", in="query", @OA\Schema(type="string", enum={"cheque", "epargne", "courant"})),
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
            CompteResource::collection($comptes),
            "Liste des comptes récupérée avec succès"
        );
    }

    /**
     * Liste des comptes non archivés
     *
     * @OA\Get(
     *     path="/v1/comptes/non-archives",
     *     tags={"Comptes"},
     *     summary="Liste des comptes non archivés",
     *     description="Récupère tous les comptes non archivés avec possibilité de filtrage",
     *     @OA\Parameter(name="page", in="query", @OA\Schema(type="integer", default=1)),
     *     @OA\Parameter(name="limit", in="query", @OA\Schema(type="integer", default=10)),
     *     @OA\Parameter(name="type", in="query", @OA\Schema(type="string", enum={"cheque", "epargne", "courant"})),
     *     @OA\Parameter(name="statut", in="query", @OA\Schema(type="string")),
     *     @OA\Parameter(name="search", in="query", @OA\Schema(type="string")),
     *     @OA\Parameter(name="sort", in="query", @OA\Schema(type="string", default="id")),
     *     @OA\Parameter(name="order", in="query", @OA\Schema(type="string", enum={"asc","desc"}, default="desc")),
     *     @OA\Response(
     *         response=200,
     *         description="Liste des comptes non archivés récupérée avec succès",
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
    public function getComptesNotArchived(Request $request)
    {
        $filters = $request->all();
        $page = (int) $request->get('page', 1);
        $limit = (int) $request->get('limit', 10);

        $comptes = $this->compteService->getAllNonArchived($filters, $page, $limit);

        return $this->successResponse($comptes, "Liste des comptes non archives récupérée avec succès");
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
     *                 @OA\Property(property="titulaire", type="string", example="John Doe"),
     *                 @OA\Property(property="nci", type="string", example="1123456789", description="Numéro NCI de 13 chiffres commençant par 1 ou 2"),
     *                 @OA\Property(property="telephone", type="string", example="771234567", description="Numéro téléphone sénégalais"),
     *                 @OA\Property(property="email", type="string", format="email", example="john.doe@example.com"),
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
        $validatedData = $request->validated();

        $compte = $this->compteService->createCompte($validatedData);

//        return $this->successResponse(
//            $compte->load('client'),
//            "Compte créé avec succès",
//            201
//        );

        return $this->successResponse(
            $compte->load('client'),
            "Compte créé avec succès",
            Response::HTTP_CREATED
        );
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
            new \App\Http\Resources\CompteResource($compte),
            "Compte récupéré avec succès"
        );
    }



}
