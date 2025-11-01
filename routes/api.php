<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CompteController;
use App\Http\Controllers\ClientController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/



Route::group(['prefix' => 'v1'], function () {
    // Auth
    Route::post('auth/login', [AuthController::class, 'login']);
    Route::post('auth/define-password', [AuthController::class, 'definePassword']);
    Route::post('auth/refresh', [AuthController::class, 'refresh']);
    Route::post('auth/logout', [AuthController::class, 'logout'])->middleware('auth:api');

    // Routes publiques (inchangées)
    Route::get('comptes/archives', [CompteController::class, 'getComptesAllArchived'])->name('comptes.archives');

    // Routes protégées par authentification et scopes
    Route::middleware(['auth:api'])->group(function () {
        // Routes pour les comptes - accessibles aux clients et admins
        Route::middleware('scope:compte:read')->group(function () {
            Route::get('comptes', [CompteController::class, 'index']);
            Route::get('comptes/{compte}', [CompteController::class, 'show']);
            Route::get('comptes/{id}/details', [CompteController::class, 'showDetails']);
        });

        Route::middleware('scope:compte:write,admin:write')->group(function () {
            Route::post('comptes', [CompteController::class, 'store']);
            Route::put('comptes/{compte}', [CompteController::class, 'update']);
            Route::delete('comptes/{compte}', [CompteController::class, 'destroy']);
            Route::post('comptes/{compte}/bloquer', [CompteController::class, 'bloquer'])->name('comptes.bloquer');
        });

        // Routes pour les clients - accessibles aux clients et admins
        Route::middleware('scope:client:read,admin:read')->group(function () {
            Route::get('clients/{numeroOrId}', [ClientController::class, 'showByNumeroOrId'])->name('clients.show_by_numero_or_id');
        });

        Route::middleware('scope:client:write,admin:write')->group(function () {
            Route::put('clients/me', [ClientController::class, 'updateMe'])->name('clients.update_me');
        });

        // Routes pour les transactions - accessibles aux clients et admins
        Route::middleware('scope:transaction:read,admin:read')->group(function () {
            // Ajouter les routes de lecture pour les transactions
        });

        Route::middleware('scope:transaction:write,admin:write')->group(function () {
            // Ajouter les routes d'écriture pour les transactions
        });
    });

    // Routes protégées avec throttling
    Route::middleware(['auth:api', 'throttle:user', 'throttle:ip'])->group(function () {
        Route::middleware('admin')->group(function () {
            // Exemples de routes admin protégées
            Route::get('admin/dashboard', function () {
                return response()->json(['message' => 'Bienvenue Admin']);
            });
            // ...autres routes admin
        });
        Route::middleware('client')->group(function () {
            // Exemples de routes client protégées
            Route::get('client/dashboard', function () {
                return response()->json(['message' => 'Bienvenue Client']);
            });
            // ...autres routes client
        });
    });
});
