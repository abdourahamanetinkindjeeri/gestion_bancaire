<?php

namespace App\Listeners;

use App\Events\CompteCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendClientNotification implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(CompteCreated $event): void
    {
        $compte = $event->compte;
        $client = $compte->client;

        if (!$client || !$client->email) {
            Log::warning("Aucun client ou email associé au compte {$compte->id}");
            return;
        }

        $subject = 'Création de votre compte bancaire';
        $message = "Bonjour {$client->titulaire},\n\n" .
                   "Votre compte bancaire a été créé avec succès.\n\n" .
                   "Détails du compte :\n" .
                   "- Numéro de compte : {$compte->numero_compte}\n" .
                   "- Type : {$compte->type}\n" .
                   "- Solde initial : {$compte->solde_initial} {$compte->devise}\n\n" .
                   "Vous pouvez maintenant effectuer des opérations sur votre compte.\n\n" .
                   "Cordialement,\n" .
                   "L'équipe de gestion bancaire";

        try {
            Mail::raw($message, function ($mail) use ($client, $subject) {
                $mail->to($client->email)->subject($subject);
            });
            Log::info("Email de création de compte envoyé à {$client->email} pour le compte {$compte->numero_compte}");
        } catch (\Throwable $e) {
            Log::error("Erreur lors de l'envoi de l'email de création de compte : " . $e->getMessage());
            // Ne pas relancer l'exception pour éviter de casser la création du compte
        }
    }
}
