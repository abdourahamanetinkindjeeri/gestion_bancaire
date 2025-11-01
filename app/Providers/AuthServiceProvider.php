<?php

namespace App\Providers;

use App\Models\Admin;
use App\Models\Client;
use App\Models\Compte;
use App\Models\Transaction;
use App\Policies\AdminPolicy;
use App\Policies\ClientPolicy;
use App\Policies\ComptePolicy;
use App\Policies\TransactionPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Admin::class => AdminPolicy::class,
        Client::class => ClientPolicy::class,
        Compte::class => ComptePolicy::class,
        Transaction::class => TransactionPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        // Expiration des tokens
        Passport::tokensExpireIn(now()->addHour());
        Passport::refreshTokensExpireIn(now()->addDays(30));

        // Charger les clés RSA depuis storage
        Passport::loadKeysFrom(storage_path());

        // Définir les scopes disponibles
        Passport::tokensCan([
            'admin:read' => 'Lire les données administrateur',
            'admin:write' => 'Écrire les données administrateur',
            'admin:delete' => 'Supprimer les données administrateur',
            'client:read' => 'Lire les données client',
            'client:write' => 'Écrire les données client',
            'compte:read' => 'Lire les données compte',
            'compte:write' => 'Écrire les données compte',
            'compte:delete' => 'Supprimer les données compte',
            'transaction:read' => 'Lire les données transaction',
            'transaction:write' => 'Écrire les données transaction',
        ]);
    }
}
