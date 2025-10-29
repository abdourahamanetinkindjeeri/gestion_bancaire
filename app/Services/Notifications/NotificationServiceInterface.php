<?php

namespace App\Services\Notifications;

use App\Http\Resources\NotificationResponse;

interface NotificationServiceInterface
{
    /**
     * Envoie un message de notification
     *
     * @param string $to Destinataire (email ou téléphone)
     * @param string|null $subject Sujet (utile pour email, ignoré pour SMS)
     * @param string $message Corps du message
     * @return NotificationResponse
     */
    public function send(string $to, ?string $subject, string $message): NotificationResponse;
}
