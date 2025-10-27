<?php

namespace App\Listeners;

use App\Events\CompteCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

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
        // Logique pour envoyer une notification au client
        // Par exemple, envoyer un email ou une notification push
        // Vous pouvez implémenter la logique ici selon vos besoins
    }
}
