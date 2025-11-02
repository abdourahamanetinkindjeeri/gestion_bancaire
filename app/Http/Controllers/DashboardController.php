<?php

namespace App\Http\Controllers;

use App\Models\Compte;
use App\Models\Transaction;
use App\Models\Client;
use Illuminate\Http\Request;
use App\Traits\ApiResponser;
use Carbon\Carbon;

/**
 * @OA\Tag(
 *     name="Dashboard",
 *     description="Tableaux de bord administrateur et client"
 * )
 */
class DashboardController extends Controller
{
    use ApiResponser;

    /**
     * Dashboard administrateur
     *
     * @OA\Get(
     *     path="/v1/admin/dashboard",
     *     tags={"Dashboard"},
     *     summary="Dashboard administrateur",
     *     description="Récupère toutes les informations du dashboard administrateur",
     *     security={{"bearerAuth": {"admin:read"}}},
     *     @OA\Response(
     *         response=200,
     *         description="Dashboard récupéré avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Dashboard récupéré avec succès"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="total_comptes", type="integer", example=150),
     *                 @OA\Property(property="balance_totale", type="number", example=25000000),
     *                 @OA\Property(property="nombre_transactions", type="integer", example=1200),
     *                 @OA\Property(property="dernieres_transactions", type="array", @OA\Items(type="object")),
     *                 @OA\Property(property="comptes_crees_aujourdhui", type="array", @OA\Items(type="object"))
     *             )
     *         )
     *     )
     * )
     */
    public function adminDashboard(Request $request)
    {
        try {
            // Total des comptes
            $totalComptes = Compte::notArchived()->count();

            // Balance totale (somme dépôts - somme retraits)
            $totalDepots = Transaction::where('type', 'depot')->sum('montant');
            $totalRetraits = Transaction::where('type', 'retrait')->sum('montant');
            $balanceTotale = $totalDepots - $totalRetraits;

            // Nombre total de transactions
            $nombreTransactions = Transaction::count();

            // 10 dernières transactions (format simplifié)
            $dernieresTransactions = Transaction::select([
                    'id', 'numero', 'compte_id', 'type', 'montant', 'devise',
                    'statut', 'date_transaction', 'created_at'
                ])
                ->with([
                    'compte:id,numero_compte,type,client_id',
                    'compte.client:id,user_id',
                    'compte.client.user:id,name,email,telephone'
                ])
                ->orderBy('date_transaction', 'desc')
                ->limit(10)
                ->get()
                ->map(function ($transaction) {
                    return [
                        'id' => $transaction->id,
                        'numero' => $transaction->numero,
                        'type' => $transaction->type,
                        'montant' => (float) $transaction->montant,
                        'devise' => $transaction->devise,
                        'statut' => $transaction->statut,
                        'date_transaction' => $transaction->date_transaction->format('Y-m-d H:i:s'),
                        'compte' => [
                            'numero_compte' => $transaction->compte->numero_compte,
                            'type' => $transaction->compte->type,
                            'client' => [
                                'nom' => $transaction->compte->client->user->name,
                                'email' => $transaction->compte->client->user->email,
                                'telephone' => $transaction->compte->client->user->telephone,
                            ]
                        ]
                    ];
                });

            // Comptes créés aujourd'hui (format simplifié)
            $comptesCreesAujourdhui = Compte::select(['id', 'numero_compte', 'type', 'solde_initial', 'devise', 'created_at', 'client_id'])
                ->whereDate('created_at', Carbon::today())
                ->with([
                    'client:id,user_id',
                    'client.user:id,name,email,telephone'
                ])
                ->get()
                ->map(function ($compte) {
                    return [
                        'id' => $compte->id,
                        'numero_compte' => $compte->numero_compte,
                        'type' => $compte->type,
                        'solde_initial' => (float) $compte->solde_initial,
                        'devise' => $compte->devise,
                        'date_creation' => $compte->created_at->format('Y-m-d H:i:s'),
                        'client' => [
                            'nom' => $compte->client->user->name,
                            'email' => $compte->client->user->email,
                            'telephone' => $compte->client->user->telephone,
                        ]
                    ];
                });

            $data = [
                'total_comptes' => $totalComptes,
                'balance_totale' => (float) $balanceTotale,
                'nombre_transactions' => $nombreTransactions,
                'dernieres_transactions' => $dernieresTransactions,
                'comptes_crees_aujourdhui' => $comptesCreesAujourdhui,
            ];

            return $this->successResponse($data, "Dashboard administrateur récupéré avec succès");

        } catch (\Throwable $e) {
            return $this->errorResponse(
                "Erreur lors de la récupération du dashboard administrateur",
                500
            );
        }
    }

    /**
     * Dashboard client
     *
     * @OA\Get(
     *     path="/v1/client/dashboard",
     *     tags={"Dashboard"},
     *     summary="Dashboard client",
     *     description="Récupère toutes les informations du dashboard client",
     *     security={{"bearerAuth": {"client:read"}}},
     *     @OA\Response(
     *         response=200,
     *         description="Dashboard récupéré avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Dashboard récupéré avec succès"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="nombre_comptes", type="integer", example=3),
     *                 @OA\Property(property="balance_totale", type="number", example=1500000),
     *                 @OA\Property(property="nombre_transactions", type="integer", example=45),
     *                 @OA\Property(property="dernieres_transactions", type="array", @OA\Items(type="object")),
     *                 @OA\Property(property="comptes", type="array", @OA\Items(type="object"))
     *             )
     *         )
     *     )
     * )
     */
    public function clientDashboard(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user->client) {
                return $this->errorResponse("Utilisateur non autorisé", 403);
            }

            $clientId = $user->client->id;

            // Nombre de comptes du client
            $nombreComptes = Compte::where('client_id', $clientId)->notArchived()->count();

            // Balance totale de tous les comptes du client (somme des soldes actuels)
            $balanceTotale = Compte::where('client_id', $clientId)->notArchived()->sum('solde_initial')
                + Transaction::whereHas('compte', function ($query) use ($clientId) {
                    $query->where('client_id', $clientId)->notArchived();
                })->where('type', 'depot')->sum('montant')
                - Transaction::whereHas('compte', function ($query) use ($clientId) {
                    $query->where('client_id', $clientId)->notArchived();
                })->where('type', 'retrait')->sum('montant');

            // Nombre total de transactions sur tous les comptes du client
            $nombreTransactions = Transaction::whereHas('compte', function ($query) use ($clientId) {
                $query->where('client_id', $clientId);
            })->count();

            // 10 dernières transactions sur tous les comptes du client (format simplifié)
            $dernieresTransactions = Transaction::select([
                    'id', 'numero', 'compte_id', 'type', 'montant', 'devise',
                    'statut', 'date_transaction', 'created_at'
                ])
                ->whereHas('compte', function ($query) use ($clientId) {
                    $query->where('client_id', $clientId);
                })
                ->with([
                    'compte:id,numero_compte,type'
                ])
                ->orderBy('date_transaction', 'desc')
                ->limit(10)
                ->get()
                ->map(function ($transaction) {
                    return [
                        'id' => $transaction->id,
                        'numero' => $transaction->numero,
                        'type' => $transaction->type,
                        'montant' => (float) $transaction->montant,
                        'devise' => $transaction->devise,
                        'statut' => $transaction->statut,
                        'date_transaction' => $transaction->date_transaction->format('Y-m-d H:i:s'),
                        'compte' => [
                            'numero_compte' => $transaction->compte->numero_compte,
                            'type' => $transaction->compte->type,
                        ]
                    ];
                });

            // Liste des comptes du client (format simplifié)
            $comptes = Compte::select([
                    'id', 'numero_compte', 'type', 'devise',
                    'statut', 'created_at'
                ])
                ->where('client_id', $clientId)
                ->notArchived()
                ->get()
                ->map(function ($compte) {
                    return [
                        'id' => $compte->id,
                        'numero_compte' => $compte->numero_compte,
                        'type' => $compte->type,
                        'solde' => (float) $compte->solde,
                        'devise' => $compte->devise,
                        'statut' => $compte->statut,
                        'date_creation' => $compte->created_at->format('Y-m-d H:i:s'),
                    ];
                });

            $data = [
                'nombre_comptes' => $nombreComptes,
                'balance_totale' => (float) $balanceTotale,
                'nombre_transactions' => $nombreTransactions,
                'dernieres_transactions' => $dernieresTransactions,
                'comptes' => $comptes,
            ];

            return $this->successResponse($data, "Dashboard client récupéré avec succès");

        } catch (\Throwable $e) {
            return $this->errorResponse(
                "Erreur lors de la récupération du dashboard client",
                500
            );
        }
    }
}
