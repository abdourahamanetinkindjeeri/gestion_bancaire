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
    // Route::get('comptes/non-archives', [CompteController::class, 'getComptesNotArchived'])
    // ->name('compte.non_archive');
    Route::get('comptes/archives', [CompteController::class, 'getComptesAllArchived'])
        ->name('comptes.archives');
    // Route::apiResource('/comptes', CompteController::class)->only(['index', 'store', 'show', 'update', 'destroy']);
    Route::apiResource('/comptes', CompteController::class);
    Route::post('comptes/{compte}/bloquer', [CompteController::class, 'bloquer'])
        ->name('comptes.bloquer');
    Route::get('/comptes/{id}/details', [CompteController::class, 'showDetails']);
    // Route::post('comptes/{compte}/debloquer', [CompteController::class, 'debloquer'])
    //     ->name('comptes.debloquer');

    // Routes pour les clients
    Route::get('/clients/{numeroOrId}', [ClientController::class, 'showByNumeroOrId'])
        ->name('clients.show_by_numero_or_id');
});


// Routes d'authentification Passport
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// Routes protégées
Route::middleware('auth:api')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Routes pour les admins
    Route::middleware('role:admin')->group(function () {
        Route::get('/admin/dashboard', function () {
            return response()->json(['message' => 'Bienvenue Admin']);
        });
        // Autres routes admin
    });

    // Routes pour les clients
    Route::middleware('role:client')->group(function () {
        Route::get('/client/dashboard', function () {
            return response()->json(['message' => 'Bienvenue Client']);
        });
        // Autres routes client
    });
});
