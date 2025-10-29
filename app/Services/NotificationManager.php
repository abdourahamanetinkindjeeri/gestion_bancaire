<?php

namespace App\Services;

use App\Services\Notifications\NotificationServiceInterface;

class NotificationManager
{
    protected NotificationServiceInterface $notifier;

    public function __construct(NotificationServiceInterface $notifier)
    {
        $this->notifier = $notifier;
    }

    public function send(string $to, string $subject, string $message): \App\Http\Resources\NotificationResponse
    {
        return $this->notifier->send($to, $subject, $message);
    }
}
