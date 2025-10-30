<?php

return [

    'defaults' => [
        'guard' => 'api', // <-- Utiliser API par défaut
        'passwords' => 'users',
    ],

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],

        // ✅ Guard API pour Laravel Passport
        'api' => [
            'driver' => 'passport', // Passport gère les tokens OAuth2
            'provider' => 'users',
            'hash' => false,       // Ne pas hasher le token
        ],
    ],

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => App\Models\User::class,
        ],

        // 'users' => [
        //     'driver' => 'database',
        //     'table' => 'users',
        // ],
    ],

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => 'password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    'password_timeout' => 10800,
];
