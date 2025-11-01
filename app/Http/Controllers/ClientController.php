<?php

namespace App\Http\Controllers;

use App\Services\ClientService;
use App\Http\Requests\ClientUpdateRequest;
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

    /**
     *     @OA\Put(
     *     path="/v1/clients/me",
     *     tags={"Clients"},
     *     summary="Mettre à jour les informations du client connecté",
     *     description="Permet à un client de modifier ses propres informations (nom, email, téléphone)",
     *     security={{"bearerAuth": {"client:write"}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Jean Dupont"),
     *             @OA\Property(property="email", type="string", format="email", example="jean.dupont@example.com"),
     *             @OA\Property(property="telephone", type="string", example="781234567")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Informations du client mises à jour avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="http_code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Informations mises à jour avec succès"),
     *             @OA\Property(property="data", type="object")
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
    public function updateMe(ClientUpdateRequest $request)
    {
        try {
            $user = auth()->user();
            $validatedData = $request->validated();

            // Mettre à jour les informations de l'utilisateur
            $user->update($validatedData);

            return $this->successResponse(
                $user->load('client'),
                "Informations mises à jour avec succès"
            );
        } catch (\Throwable $e) {
            return $this->errorResponse(
                "Erreur lors de la mise à jour des informations",
                422
            );
        }
    }
}
