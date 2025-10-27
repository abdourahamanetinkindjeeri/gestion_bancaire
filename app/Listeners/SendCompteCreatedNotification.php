<?php

namespace App\Listeners;

use App\Events\CompteCreated;
use App\Services\NotificationManager;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendCompteCreatedNotification implements ShouldQueue
{
    use InteractsWithQueue;

    protected NotificationManager $notificationManager;

    /**
     * Injecte le gestionnaire de notifications.
     */
    public function __construct(NotificationManager $notificationManager)
    {
        $this->notificationManager = $notificationManager;
    }

    /**
     * Gère l'événement.
     */
    public function handle(CompteCreated $event): void
    {
        $compte = $event->compte;
        $client = $compte->client; // relation "client" sur le modèle Compte

        if (!$client) {
            \Log::warning("Aucun client associé au compte {$compte->id}");
            return;
        }

        $this->notificationManager->send(
            $client->telephone,
            'Création de compte',
            "Bonjour {$client->titulaire}, votre compte {$compte->numero_compte} a été créé avec succès."
        );
    }
}
