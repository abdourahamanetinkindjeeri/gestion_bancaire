<?php

namespace App\Listeners;

use App\Events\CompteCreated;
use App\Services\NotificationManager;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

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
            Log::warning("Aucun client associé au compte {$compte->id}");
            return;
        }

        // Récupérer les informations utilisateur depuis la relation user
        $user = $client->user;
        if (!$user) {
            Log::warning("Aucun utilisateur associé au client {$client->id}");
            return;
        }

        Log::info($client);
        // Envoi de SMS via Twilio
        if ($user->telephone) {
            $twilioService = app(\App\Services\Notifications\TwilioNotificationService::class);
            $smsResponse = $twilioService->send(
                '+221' . $user->telephone, // Format international pour le Sénégal
                null,
                "Bonjour {$user->name}, votre compte {$compte->numero_compte} a été créé avec succès."
            );
        } else {
            Log::warning("Aucun numéro de téléphone pour l'utilisateur {$user->id}");
            $smsResponse = (object) ['success' => false, 'error' => 'No phone number'];
        }

        if (!$smsResponse->success) {
            Log::error("Échec envoi SMS à {$client->telephone}: {$smsResponse->error}");
        }

        // Envoi d'email via Mail
        $mailService = app(\App\Services\Notifications\MailNotificationService::class);
        $emailResponse = $mailService->send(
            $user->email,
            'Création de compte',
            "Bonjour {$user->name},\n\nVotre compte bancaire {$compte->numero_compte} a été créé avec succès.\n\nType de compte: {$compte->type}\nSolde initial: {$compte->solde_initial} {$compte->devise}\n\nCordialement,\nL'équipe bancaire"
        );

        if (!$emailResponse->success) {
            Log::error("Échec envoi email à {$client->email}: {$emailResponse->error}");
        }
    }
}
