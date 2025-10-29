<?php

namespace App\Services\Notifications;

use App\Http\Resources\NotificationResponse;
use Twilio\Rest\Client;
use Illuminate\Support\Facades\Log;
use Twilio\Exceptions\RestException;

class TwilioNotificationService implements NotificationServiceInterface
{
    protected Client $twilio;
    protected string $from;

    public function __construct(Client $twilio)
    {
        $this->twilio = $twilio;
        $this->from   = config('services.twilio.from');
    }

    public function send(string $to, ?string $subject, string $message): NotificationResponse
    {
        try {
            $response = $this->twilio->messages->create($to, [
                'from' => $this->from,
                'body' => $message,
            ]);

            return new NotificationResponse(
                success: true,
                to: $to,
                sid: $response->sid,
                status: $response->status
            );
        } catch (RestException $e) {
            Log::error("Twilio REST error [{$e->getStatusCode()}]: " . $e->getMessage());

            return new NotificationResponse(
                success: false,
                to: $to,
                error: $e->getMessage()
            );
        } catch (\Throwable $e) {
            Log::error("Erreur Twilio générique : " . $e->getMessage());

            return new NotificationResponse(
                success: false,
                to: $to,
                error: $e->getMessage()
            );
        }
    }
}
