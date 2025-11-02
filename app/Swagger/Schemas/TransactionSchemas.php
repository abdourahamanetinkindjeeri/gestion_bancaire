<?php

namespace App\Swagger\Schemas;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="Transaction",
 *     type="object",
 *     title="Transaction",
 *     description="Modèle représentant une transaction bancaire",
 *     @OA\Property(property="id", type="string", format="uuid", description="Identifiant unique de la transaction"),
 *     @OA\Property(property="numero", type="string", description="Numéro de transaction généré automatiquement"),
 *     @OA\Property(property="compte_id", type="string", format="uuid", description="ID du compte associé"),
 *     @OA\Property(property="type", type="string", enum={"depot", "retrait"}, description="Type de transaction"),
 *     @OA\Property(property="montant", type="number", format="decimal", description="Montant de la transaction"),
 *     @OA\Property(property="devise", type="string", description="Devise de la transaction", example="FCFA"),
 *     @OA\Property(property="statut", type="string", description="Statut de la transaction", example="complete"),
 *     @OA\Property(property="date_transaction", type="string", format="date-time", description="Date et heure de la transaction"),
 *     @OA\Property(property="metadata", type="object", nullable=true, description="Données additionnelles"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time"),
 *     @OA\Property(property="compte", type="object", description="Informations du compte associé",
 *         @OA\Property(property="id", type="string", format="uuid"),
 *         @OA\Property(property="numero_compte", type="string"),
 *         @OA\Property(property="type", type="string"),
 *         @OA\Property(property="devise", type="string")
 *     )
 * )
 */
class TransactionSchemas
{
    // Ce fichier est uniquement pour Swagger-PHP, il peut rester vide.
}
