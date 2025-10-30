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
    Route::post('auth/refresh', [AuthController::class, 'refresh']);
    Route::post('auth/logout', [AuthController::class, 'logout'])->middleware('auth:api');

    // Routes publiques (inchangées)
    Route::get('comptes/archives', [CompteController::class, 'getComptesAllArchived'])->name('comptes.archives');
    Route::apiResource('comptes', CompteController::class);
    Route::post('comptes/{compte}/bloquer', [CompteController::class, 'bloquer'])->name('comptes.bloquer');
    Route::get('comptes/{id}/details', [CompteController::class, 'showDetails']);
    Route::get('clients/{numeroOrId}', [ClientController::class, 'showByNumeroOrId'])->name('clients.show_by_numero_or_id');

    // Routes protégées
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
