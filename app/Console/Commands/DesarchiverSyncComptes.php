<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\DesarchiverCompteService;

class DesarchiverSyncComptes extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'desarchive:sync';

    /**
     * The console command description.
     */
    protected $description = 'Désarchive immédiatement tous les comptes depuis la base Neon sans passer par la queue';

    protected DesarchiverCompteService $desarchiverService;

    public function __construct(DesarchiverCompteService $desarchiverService)
    {
        parent::__construct();
        $this->desarchiverService = $desarchiverService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $neon = DB::connection('neon');

        // 🔹 On récupère tous les comptes archivés
        $comptesArchives = $neon->table('comptes_bloque')->get();
        $count = $comptesArchives->count();

        $this->info("🔎 {$count} comptes trouvés dans la base d’archivage pour désarchivage.");

        if ($count === 0) {
            return;
        }

        foreach ($comptesArchives as $compte) {
            $this->info("📤 Désarchivage du compte {$compte->numero_compte} ({$compte->id})...");
            Log::info("[DESARCHIVE_SYNC] Démarrage désarchivage compte", [
                'compte_id' => $compte->id,
                'numero_compte' => $compte->numero_compte
            ]);

            $this->desarchiverService->desarchiverCompte($compte->id);

            $this->info("✅ Compte {$compte->numero_compte} désarchivé.");
        }

        $this->info("🎯 Désarchivage terminé pour {$count} comptes archivés.");
        Log::info("[DESARCHIVE_SYNC] Désarchivage terminé pour {$count} comptes.");
    }
}
