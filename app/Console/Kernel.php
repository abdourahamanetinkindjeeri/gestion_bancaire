<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Jobs\VerifierBlocageJob;
use App\Jobs\VerifierDeblocageJob;
use Illuminate\Support\Facades\Artisan;

class Kernel extends ConsoleKernel
{
    /**
     * Définition des tâches planifiées
     */
    protected function schedule(Schedule $schedule): void
    {
        /**
         * 🕒 1️⃣ Archivage des comptes bloqués
         * Exécution quotidienne à 02:48
         */
        $schedule->call(function () {
            Artisan::call('archive:sync');
        })
            ->dailyAt('02:48')
            ->name('archivage-comptes-bloques')
            ->withoutOverlapping()
            ->onOneServer();

        /**
         * 🕒 2️⃣ Désarchivage des comptes débloqués
         * Exécution quotidienne à 02:55
         */
        $schedule->call(function () {
            Artisan::call('desarchive:sync');
        })
            ->dailyAt('02:55')
            ->name('desarchivage-comptes-bloques')
            ->withoutOverlapping()
            ->onOneServer();

        /**
         * 🧩 3️⃣ Vérification des comptes à bloquer
         * Exécution quotidienne à 02:48 (après l’archivage)
         */
        $schedule->job(new VerifierBlocageJob())
            ->dailyAt('02:48')
            ->onQueue('archivage')
            ->name('verification-blocages')
            ->withoutOverlapping()
            ->onOneServer();

        /**
         * 🧩 4️⃣ Vérification des comptes à débloquer
         * Exécution quotidienne à 02:55 (après le désarchivage)
         */
        $schedule->job(new VerifierDeblocageJob())
            ->dailyAt('02:55')
            ->onQueue('desarchivage')
            ->name('verification-deblocages')
            ->withoutOverlapping()
            ->onOneServer();

        /**
         * 💾 5️⃣ Archivage hebdomadaire des transactions
         * Tous les lundis à minuit
         */
        $schedule->command('transactions:archive')
            ->sundays()
            ->at('22:50')
            ->withoutOverlapping()
            ->onOneServer()
            ->sendOutputTo(storage_path('logs/transactions_archive.log'));
    }

    /**
     * Enregistrement des commandes artisan personnalisées
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');
        require base_path('routes/console.php');
    }
}
