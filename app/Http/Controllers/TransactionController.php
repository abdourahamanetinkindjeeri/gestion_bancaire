<?php

namespace App\Http\Controllers;

use App\Http\Requests\TransactionDepotRequest;
use App\Models\Transaction;
use App\Services\TransactionService;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @OA\Tag(
 *     name="Transactions",
 *     description="Gestion des transactions bancaires"
 * )
 */
class TransactionController extends Controller
{
    use ApiResponser;

    protected TransactionService $transactionService;

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    public function index(Request $request)
    {
        $user = $request->user();

        // Vérifier que l'utilisateur est soit un admin soit un client
        if (!$user || (!$user->admin && !$user->client)) {
            return $this->errorResponse("Accès non autorisé", 403);
        }

        $filters = $request->all();
        $page = (int) $request->get('page', 1);
        $limit = (int) $request->get('limit', 10);


        // Les admins voient toutes les transactions (pas de filtre client_id)

        $transactions = $this->transactionService->getAllByUser($user,$filters, $page, $limit);

        return $this->successResponse(
            $transactions,
            "Liste des transactions récupérée avec succès"
        );
    }


    public function depot(TransactionDepotRequest $request)
    {
        try {
            $validatedData = $request->validated();

            $transaction = $this->transactionService->effectuerDepot($validatedData);

            return $this->successResponse(
                $transaction,
                "Dépôt effectué avec succès",
                Response::HTTP_CREATED
            );
        } catch (\Throwable $e) {
            return $this->errorResponse(
                $e->getMessage(),
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }
    }


    public function getTransactionsByCompte(Request $request, string $compteId)
    {
        try {
            $filters = $request->all();
            $page = (int) $request->get('page', 1);
            $limit = (int) $request->get('limit', 10);

            // Vérifier que l'utilisateur peut voir les transactions de ce compte
            $user = $request->user();

            // Les admins peuvent voir tous les comptes
            if (!$user->admin && $user->client && !$user->client->comptes()->where('id', $compteId)->exists()) {
                return $this->errorResponse("Accès non autorisé à ce compte", 403);
            }

            $transactions = $this->transactionService->getTransactionsByCompte($compteId, $filters, $page, $limit);

            return $this->successResponse(
                $transactions,
                "Liste des transactions récupérée avec succès"
            );
        } catch (\Throwable $e) {
            return $this->errorResponse(
                $e->getMessage(),
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }
    }

    /**
     * Statistiques des transactions d'un compte
     *
     * @OA\Get(
     *     path="/v1/transactions/compte/{compte_id}/statistiques",
     *     tags={"Transactions"},
     *     summary="Statistiques des transactions d'un compte",
     *     description="Récupère les statistiques des transactions d'un compte spécifique",
     *     security={{"bearerAuth": {"transaction:read"}}},
     *     @OA\Parameter(name="compte_id", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(
     *         response=200,
     *         description="Statistiques récupérées avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="http_code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Statistiques récupérées avec succès"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="total_depot", type="number", example=150000),
     *                 @OA\Property(property="total_retrait", type="number", example=50000),
     *                 @OA\Property(property="nombre_transactions", type="integer", example=5),
     *                 @OA\Property(property="derniere_transaction", type="object")
     *             )
     *         )
     *     )
     * )
     */
    public function getStatistiquesByCompte(Request $request, string $compteId)
    {
        try {
            // Vérifier que l'utilisateur peut voir les statistiques de ce compte
            $user = $request->user();

            // Les admins peuvent voir tous les comptes
            if (!$user->admin && $user->client && !$user->client->comptes()->where('id', $compteId)->exists()) {
                return $this->errorResponse("Accès non autorisé à ce compte", 403);
            }

            $statistiques = $this->transactionService->getStatistiquesByCompte($compteId);

            return $this->successResponse(
                $statistiques,
                "Statistiques récupérées avec succès"
            );
        } catch (\Throwable $e) {
            return $this->errorResponse(
                $e->getMessage(),
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }
    }
}
