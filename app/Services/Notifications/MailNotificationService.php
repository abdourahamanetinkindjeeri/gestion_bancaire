<?php

namespace App\Services\Notifications;

use App\Http\Resources\NotificationResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class MailNotificationService implements NotificationServiceInterface
{
    public function send(string $to, ?string $subject, string $message): NotificationResponse
    {
        try {
            Mail::raw($message, function ($mail) use ($to, $subject) {
                $mail->to($to)
                     ->subject($subject ?? 'Notification');
            });

            return new NotificationResponse(
                success: true,
                to: $to,
                status: 'sent'
            );
        } catch (\Throwable $e) {
            Log::error("Erreur lors de l'envoi d'email à {$to}: " . $e->getMessage());

            return new NotificationResponse(
                success: false,
                to: $to,
                error: $e->getMessage()
            );
        }
    }
}
