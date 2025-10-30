<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Jobs\VerifierBlocageJob;
use App\Jobs\VerifierDeblocageJob;
use Illuminate\Support\Facades\Artisan;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        // 🔹 Archivage quotidien des comptes bloqués à 15h10
        $schedule->call(function () {
            Artisan::call('archive:sync');
        })
            ->dailyAt('03:15')
            ->name('archivage-comptes-bloques')
            ->withoutOverlapping();

        $schedule->call(function () {
            Artisan::call('desarchive:sync');
        })
            ->dailyAt('03:06')
            ->name('archivage-comptes-bloques')
            ->withoutOverlapping();

        // 🔹 Vérification quotidienne des blocages à 15h10
        $schedule->call(function () {
            VerifierBlocageJob::dispatch()->onQueue('archivage');
        })
            ->dailyAt('03:06')
            ->name('verification-blocages')
            ->withoutOverlapping();

        $schedule->call(function () {
            VerifierDeblocageJob::dispatch()->onQueue(('desarchivage'))
                ->dailyAt('03:06')
                ->name('verification-deblocages')
                ->withoutOverlapping();;
        });
    }

    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');
        require base_path('routes/console.php');
    }
}
