<?php

namespace App\Http\Controllers;

use App\Services\ClientService;
use Illuminate\Http\Request;
use App\Traits\ApiResponser;

/**
 * @OA\Tag(
 *     name="Clients",
 *     description="Gestion des clients bancaires"
 * )
 */
class ClientController extends Controller
{
    use ApiResponser;

    protected ClientService $clientService;

    public function __construct(ClientService $clientService)
    {
        $this->clientService = $clientService;
    }

    /**
     *     @OA\Get(
     *     path="/v1/clients/{numeroOrId}",
     *     tags={"Clients"},
     *     summary="Récupérer un client par numéro de téléphone, email, NCI ou ID",
     *     description="Récupère les détails d'un client bancaire par son numéro de téléphone, email, numéro NCI ou ID",
     *     security={{"bearerAuth": {"client:read"}}},
     *     @OA\Parameter(
     *         name="numeroOrId",
     *         in="path",
     *         description="Numéro de téléphone, email, numéro NCI ou ID du client",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Client récupéré avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="http_code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Client récupéré avec succès"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Client introuvable",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="http_code", type="integer", example=404),
     *             @OA\Property(property="message", type="string", example="Client introuvable")
     *         )
     *     )
     * )
     */
    public function showByNumeroOrId(string $numeroOrId)
    {
        $client = $this->clientService->findByNumeroOrId($numeroOrId);

        if (!$client) {
            return $this->errorResponse("Client introuvable", 404);
        }

        return $this->successResponse(
            $client,
            "Client récupéré avec succès"
        );
    }
}
