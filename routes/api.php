<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CompteController;

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
    Route::apiResource('/comptes', CompteController::class)->only(['index', 'store', 'show']);
    Route::get('comptes/non-archives', [CompteController::class, 'getComptesNotArchived'])
        ->name('comptes.non_archives');
    Route::post('comptes/{id}/bloquer', [CompteController::class, 'bloquer'])
        ->name('comptes.bloquer');
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
