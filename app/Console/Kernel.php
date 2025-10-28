<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Jobs\VerifierBlocageJob;

class Kernel extends ConsoleKernel
{
    /**
     * Planification des tâches automatiques.
     */
    protected function schedule(Schedule $schedule): void
    {
        // 🔹 Lance le job de vérification des comptes bloqués chaque jour à 15h40
        $schedule->call(function () {
            VerifierBlocageJob::dispatch()->onQueue('archivage');
        })
            ->dailyAt('01:30')
            ->name('archivage-comptes-bloques')
            ->withoutOverlapping();
    }

    /**
     * Enregistrement des commandes artisan.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');
        require base_path('routes/console.php');
    }
}
