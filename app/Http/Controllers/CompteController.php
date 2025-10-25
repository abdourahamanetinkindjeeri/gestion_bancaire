<?php

namespace App\Http\Controllers;

use App\Http\Requests\CompteStoreRequest;
use App\Http\Resources\CompteResource;
use App\Services\CompteService;
use Illuminate\Http\Request;
use App\Traits\ApiResponser;

/**
 * @OA\Info(
 *     title="API de Gestion des Comptes",
 *     version="1.0.0",
 *     description="API pour la gestion des comptes bancaires"
 * )
 *
 * @OA\Server(
 *     url="http://localhost:8000/api",
 *     description="Serveur de développement"
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
}
