<?php

namespace App\Jobs;

use App\Services\DesarchiverCompteService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DesarchiverCompteJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * ID du compte archivé à désarchiver
     */
    public function __construct(public string $compteId) {}

    /**
     * Logique principale du job
     */
    public function handle(DesarchiverCompteService $service): void
    {
        Log::info("[JOB] DesarchiverCompteJob déclenché", ['compte_id' => $this->compteId]);

        $neon = DB::connection('neon');
        $compteArchive = $neon->table('comptes_bloque')->where('id', $this->compteId)->first();

        // 🔹 Vérification de l’existence du compte dans la base d’archivage
        if (! $compteArchive) {
            Log::warning("[JOB] Aucun compte archivé trouvé pour désarchivage", [
                'compte_id' => $this->compteId
            ]);
            return;
        }

        // 🔹 Appel du service de désarchivage
        try {
            $service->desarchiverCompte($this->compteId);
            Log::info("✅ [JOB] Compte [{$this->compteId}] désarchivé avec succès.");
        } catch (\Throwable $e) {
            Log::error("❌ [JOB] Erreur lors du désarchivage du compte [{$this->compteId}] : " . $e->getMessage());
        }
    }
}
