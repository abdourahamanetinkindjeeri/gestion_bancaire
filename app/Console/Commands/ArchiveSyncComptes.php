<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Compte;
use App\Services\ArchiveCompteService;
use Illuminate\Support\Facades\Log;

class ArchiveSyncComptes extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'archive:sync';

    /**
     * The console command description.
     */
    protected $description = 'Archive immédiatement tous les comptes bloqués sans passer par la queue';

    protected ArchiveCompteService $archiveService;

    public function __construct(ArchiveCompteService $archiveService)
    {
        parent::__construct();
        $this->archiveService = $archiveService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $comptes = Compte::with('transactions')->where('statut', 'bloque')->get();

        $count = $comptes->count();
        $this->info("🔎 {$count} comptes bloqués trouvés pour archivage.");

        if ($count === 0) {
            return;
        }

        foreach ($comptes as $compte) {
            $this->info("📦 Archivage du compte {$compte->numero_compte} ({$compte->id})...");
            Log::info("[ARCHIVE_SYNC] Démarrage archivage compte", [
                'compte_id' => $compte->id,
                'numero_compte' => $compte->numero_compte
            ]);

            $this->archiveService->archiverCompte($compte->id);

            $this->info("✅ Compte {$compte->numero_compte} archivé.");
        }

        $this->info("🎯 Archivage terminé pour {$count} comptes bloqués.");
        Log::info("[ARCHIVE_SYNC] Archivage terminé pour {$count} comptes.");
    }
}
