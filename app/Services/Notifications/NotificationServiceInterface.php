<?php

namespace App\Services\Notifications;

interface NotificationServiceInterface
{
    /**
     * Envoie un message de notification
     *
     * @param string $to Destinataire (email ou téléphone)
     * @param string $subject Sujet (pour mail uniquement)
     * @param string $message Corps du message
     * @return bool
     */
    public function send(string $to, string $subject, string $message): bool;
}
