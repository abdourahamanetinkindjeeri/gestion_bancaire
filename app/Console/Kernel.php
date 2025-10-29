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
        // 🔹 Archivage quotidien des comptes bloqués à 15h00
        $schedule->call(function () {
            Artisan::call('archive:sync');
        })
            ->dailyAt('15:10')
            ->name('archivage-comptes-bloques')
            ->withoutOverlapping();

        // 🔹 Vérification quotidienne des blocages à 15h00
        $schedule->call(function () {
            VerifierBlocageJob::dispatch()->onQueue('archivage');
        })
            ->dailyAt('15:10')
            ->name('verification-blocages')
            ->withoutOverlapping();
    }

    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');
        require base_path('routes/console.php');
    }
}
