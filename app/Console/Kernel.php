<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Jobs\VerifierBlocageJob;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        // 🔹 Tâche quotidienne à 02:00 pour archivage des comptes bloqués
        $schedule->call(function () {
            VerifierBlocageJob::dispatch()->onQueue('archivage');
        })
            ->dailyAt('15:30')
            ->name('archivage-comptes-bloques')
            ->withoutOverlapping();
    }

    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');
        require base_path('routes/console.php');
    }
}
