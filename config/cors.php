<?php
//
//return [
//
//    /*
//    |--------------------------------------------------------------------------
//    | CORS Configuration (Cross-Origin Resource Sharing)
//    |--------------------------------------------------------------------------
//    | Configure les règles d'accès entre votre backend et les clients externes.
//    |--------------------------------------------------------------------------
//    */
//
//    // Routes concernées par CORS
//    'paths' => [
//        'api/*',
//        'sanctum/csrf-cookie',
//    ],
//
//    // Méthodes HTTP autorisées
//    'allowed_methods' => [
//        'GET',
//        'POST',
//        'PUT',
//        'PATCH',
//        'DELETE',
//        'OPTIONS',
//    ],
//
//    // Origines autorisées (à adapter selon votre frontend)
//    // Origines autorisées dynamiquement via .env (CORS_ALLOWED_ORIGINS)
//    'allowed_origins' => explode(',', env('CORS_ALLOWED_ORIGINS', '*')),
//
//    // Expressions régulières pour les origines (laisser vide si non utilisé)
//    'allowed_origins_patterns' => [],
//
//    // Headers autorisés dans les requêtes
//    'allowed_headers' => [
//        'Content-Type',
//        'X-Requested-With',
//        'Authorization',
//        'Accept',
//        'Origin',
//        'X-CSRF-Token',
//    ],
//
//    // Headers exposés au navigateur
//    'exposed_headers' => [
//        'Authorization',
//        'Content-Type',
//    ],
//
//    // Durée de mise en cache du pré-vol (en secondes)
//    'max_age' => 3600, // 1 heure
//
//    // Autorise l'envoi de cookies/tokens (utile pour authentification)
//    'supports_credentials' => true,
//
//];


return [

    /*
    |--------------------------------------------------------------------------
    | CORS Configuration (Cross-Origin Resource Sharing)
    |--------------------------------------------------------------------------
    | Configure les règles d'accès entre votre backend et les clients externes.
    |--------------------------------------------------------------------------
    */

    // Routes concernées par CORS
    'paths' => [
        'api/*',
        'tinkin/*',
        'sanctum/csrf-cookie',
        'docs/*', // Swagger UI si tu as /docs
    ],

    // Méthodes HTTP autorisées
    'allowed_methods' => ['*'], // toutes les méthodes

    // Origines autorisées
    // ⚠️ Pour supports_credentials=true, ne jamais mettre '*' !
    'allowed_origins' => [
        'http://localhost:8080',        // Swagger UI local
        'http://127.0.0.1:8000',       // Laravel local
        'https://jeeri.onrender.com',   // Production
    ],

    // Expressions régulières pour les origines (laisser vide si non utilisé)
    'allowed_origins_patterns' => [],

    // Headers autorisés dans les requêtes
    'allowed_headers' => ['*'],

    // Headers exposés au navigateur
    'exposed_headers' => [
        'Authorization',
        'Content-Type',
    ],

    // Durée de mise en cache du pré-vol (en secondes)
    'max_age' => 3600,

    // Autorise l'envoi de cookies/tokens (utile si tu utilises sanctum ou JWT)
    'supports_credentials' => true,

];
