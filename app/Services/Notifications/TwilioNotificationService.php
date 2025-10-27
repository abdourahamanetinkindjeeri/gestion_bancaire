<?php

namespace App\Services\Notifications;

use Twilio\Rest\Client;
use Illuminate\Support\Facades\Log;

class TwilioNotificationService implements NotificationServiceInterface
{
    protected Client $twilio;

    public function __construct()
    {
        $this->twilio = new Client(
            config('services.twilio.sid'),
            config('services.twilio.token')
        );
    }

    public function send(string $to, string $subject, string $message): bool
    {
        try {
            $this->twilio->messages->create($to, [
                'from' => config('services.twilio.from'),
                'body' => $message,
            ]);
            return true;
        } catch (\Throwable $e) {
            Log::error("Erreur Twilio : " . $e->getMessage());
            return false;
        }
    }
}
