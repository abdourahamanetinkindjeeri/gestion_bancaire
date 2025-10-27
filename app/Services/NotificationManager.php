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

    public function send(string $to, string $subject, string $message): bool
    {
        return $this->notifier->send($to, $subject, $message);
    }
}
