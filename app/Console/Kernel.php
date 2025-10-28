<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Jobs\VerifierBlocageJob;
use Illuminate\Support\Facades\Artisan;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        // 🔹 Tâche quotidienne à 00:00 pour archivage des comptes bloqués
        $schedule->call(function () {
            Artisan::call('archive:sync');
        })
            ->dailyAt('00:00')
            ->name('archivage-comptes-bloques')
            ->withoutOverlapping();

        // 🔹 Tâche quotidienne à 02:00 pour vérification des blocages
        $schedule->call(function () {
            VerifierBlocageJob::dispatch()->onQueue('archivage');
        })
            ->dailyAt('02:00')
            ->name('verification-blocages')
            ->withoutOverlapping();
    }

    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');
        require base_path('routes/console.php');
    }
}
