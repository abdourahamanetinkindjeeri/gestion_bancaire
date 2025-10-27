<?php

namespace App\Swagger\Schemas;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="Compte",
 *     type="object",
 *     title="Compte",
 *     description="Modèle représentant un compte bancaire",
 *     @OA\Property(property="id", type="string", format="uuid", description="Identifiant unique du compte"),
 *     @OA\Property(property="numero_compte", type="string", description="Numéro de compte généré automatiquement"),
 *     @OA\Property(property="type", type="string", enum={"cheque", "epargne", "courant"}, description="Type de compte"),
 *     @OA\Property(property="solde_initial", type="number", format="decimal", description="Solde de départ"),
 *     @OA\Property(property="devise", type="string", description="Devise du compte", example="FCFA"),
 *     @OA\Property(property="statut", type="string", description="Statut du compte", example="actif"),
 *     @OA\Property(property="client_id", type="string", format="uuid", description="ID du client titulaire"),
 *     @OA\Property(property="metadata", type="object", nullable=true, description="Données additionnelles"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time"),
 *     @OA\Property(property="deleted_at", type="string", format="date-time", nullable=true)
 * )
 *
 * @OA\Schema(
 *     schema="Pagination",
 *     type="object",
 *     title="Pagination",
 *     description="Informations de pagination",
 *     @OA\Property(property="currentPage", type="integer"),
 *     @OA\Property(property="totalPages", type="integer"),
 *     @OA\Property(property="totalItems", type="integer"),
 *     @OA\Property(property="itemsPerPage", type="integer"),
 *     @OA\Property(property="hasNext", type="boolean"),
 *     @OA\Property(property="hasPrevious", type="boolean")
 * )
 */
class CompteSchemas
{
    // Ce fichier est uniquement pour Swagger-PHP, il peut rester vide.
}
