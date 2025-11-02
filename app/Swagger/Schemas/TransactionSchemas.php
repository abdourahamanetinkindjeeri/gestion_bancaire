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
 *     @OA\Property(property="type", type="string", enum={"depot", "retrait", "transfert"}, description="Type de transaction"),
 *     @OA\Property(property="montant", type="number", format="decimal", description="Montant de la transaction"),
 *     @OA\Property(property="devise", type="string", description="Devise de la transaction", example="FCFA"),
 *     @OA\Property(property="statut", type="string", enum={"en_attente", "complete", "echouee"}, description="Statut de la transaction"),
 *     @OA\Property(property="date_transaction", type="string", format="date", description="Date de la transaction"),
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

/**
 * @OA\Schema(
 *     schema="TransactionDepotRequest",
 *     type="object",
 *     title="TransactionDepotRequest",
 *     description="Requête pour effectuer un dépôt",
 *     required={"compte_id", "montant"},
 *     @OA\Property(property="compte_id", type="string", format="uuid", description="ID du compte sur lequel effectuer le dépôt", example="a040f68a-11c2-4a84-877b-1a0a43539810"),
 *     @OA\Property(property="montant", type="number", minimum=1000, description="Montant du dépôt", example=50000),
 *     @OA\Property(property="metadata", type="object", nullable=true, description="Données additionnelles",
 *         @OA\Property(property="description", type="string", example="Dépôt initial")
 *     )
 * )
 */

/**
 * @OA\Schema(
 *     schema="TransactionResponse",
 *     type="object",
 *     title="TransactionResponse",
 *     description="Réponse après création d'une transaction",
 *     @OA\Property(property="id", type="string", format="uuid", description="Identifiant unique de la transaction"),
 *     @OA\Property(property="numero", type="string", description="Numéro de transaction généré"),
 *     @OA\Property(property="compte_id", type="string", format="uuid", description="ID du compte associé"),
 *     @OA\Property(property="type", type="string", enum={"depot", "retrait", "transfert"}, description="Type de transaction"),
 *     @OA\Property(property="montant", type="number", format="decimal", description="Montant de la transaction"),
 *     @OA\Property(property="devise", type="string", description="Devise de la transaction"),
 *     @OA\Property(property="statut", type="string", enum={"en_attente", "complete", "echouee"}, description="Statut de la transaction"),
 *     @OA\Property(property="date_transaction", type="string", format="date", description="Date de la transaction"),
 *     @OA\Property(property="metadata", type="object", nullable=true, description="Données additionnelles"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time"),
 *     @OA\Property(property="compte", type="object", description="Informations du compte associé",
 *         @OA\Property(property="id", type="string", format="uuid"),
 *         @OA\Property(property="numero_compte", type="string"),
 *         @OA\Property(property="type", type="string"),
 *         @OA\Property(property="solde_initial", type="number"),
 *         @OA\Property(property="devise", type="string"),
 *         @OA\Property(property="statut", type="string")
 *     )
 * )
 */
class TransactionSchemas
{
    // Ce fichier est uniquement pour Swagger-PHP, il peut rester vide.
}
