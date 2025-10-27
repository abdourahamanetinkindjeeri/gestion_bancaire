<?php

namespace App\Services\Notifications;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class MailNotificationService implements NotificationServiceInterface
{
    public function send(string $to, string $subject, string $message): bool
    {
        try {
            Mail::raw($message, function ($mail) use ($to, $subject) {
                $mail->to($to)
                     ->subject($subject)
                     ->from(config('mail.from.address'), config('mail.from.name'));
            });
            return true;
        } catch (\Throwable $e) {
            Log::error("Erreur lors de l'envoi d'un email : " . $e->getMessage());
            return false;
        }
    }
}
