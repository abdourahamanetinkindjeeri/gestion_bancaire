<?php

namespace App\Listeners;

use App\Events\CompteCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Twilio\Rest\Client as TwilioClient;

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
        $user = $client ? $client->user : null;

        // Envoi email
        if ($client && $client->email && $user) {
            $subject = 'Création de votre compte bancaire';
            $message = "Bonjour {$user->name},\n\n" .
                "Votre compte bancaire a été créé avec succès.\n\n" .
                "Détails du compte :\n" .
                "- Numéro de compte : {$compte->numero_compte}\n" .
                "- Type : {$compte->type}\n" .
                "- Solde initial : {$compte->solde_initial} {$compte->devise}\n\n";

            // Inclure le code d'activation si le compte n'est pas activé
            if (!$user->is_activated && $user->activation_code) {
                $message .= "Code d'activation : {$user->activation_code}\n" .
                    "Ce code expire dans 30 minutes.\n\n" .
                    "Utilisez ce code pour définir votre mot de passe.\n\n";
            } else {
                $message .= "Vous pouvez maintenant effectuer des opérations sur votre compte.\n\n";
            }

            $message .= "Cordialement,\n" .
                "L'équipe de gestion bancaire";

            try {
                Mail::raw($message, function ($mail) use ($client, $subject) {
                    $mail->to($client->email)->subject($subject);
                });
                Log::info("Email de création de compte envoyé à {$client->email} pour le compte {$compte->numero_compte}");
            } catch (\Throwable $e) {
                Log::error("Erreur lors de l'envoi de l'email de création de compte : " . $e->getMessage());
            }
        }

        // Envoi SMS via Twilio
        if ($client && $client->telephone && $user) {
            try {
                $twilioSid = config('services.twilio.sid');
                $twilioToken = config('services.twilio.auth_token');
                $twilioFrom = config('services.twilio.from');
                $twilio = new TwilioClient($twilioSid, $twilioToken);
                $smsMessage = "Bonjour {$user->name}, votre compte bancaire a été créé avec succès. Numéro : {$compte->numero_compte}. Solde initial : {$compte->solde_initial} {$compte->devise}.";

                // Inclure le code d'activation si le compte n'est pas activé
                if (!$user->is_activated && $user->activation_code) {
                    $smsMessage .= " Code d'activation : {$user->activation_code}. Expire dans 30 min.";
                }

                $twilio->messages->create($client->telephone, [
                    'from' => $twilioFrom,
                    'body' => $smsMessage
                ]);
                Log::info("SMS de création de compte envoyé à {$client->telephone} pour le compte {$compte->numero_compte}");
            } catch (\Throwable $e) {
                Log::error("Erreur lors de l'envoi du SMS de création de compte : " . $e->getMessage());
            }
        }
    }
}
