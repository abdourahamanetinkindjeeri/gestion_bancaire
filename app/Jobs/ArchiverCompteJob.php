<?php

namespace App\Jobs;

use App\Models\Compte;
use App\Services\ArchiveCompteService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ArchiverCompteJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public string $compteId) {}

    public function handle(ArchiveCompteService $service): void
    {
        Log::info("[JOB] ArchiverCompteJob déclenché", ['compte_id' => $this->compteId]);

        $compte = Compte::with('transactions')->find($this->compteId);

        if (! $compte || $compte->statut !== 'bloque' || $compte->type !== 'epargne' || $compte->debut_blocage > now()) {
            Log::warning("[JOB] Compte non archivable", [
                'compte_id' => $this->compteId,
                'statut' => $compte?->statut,
                'type' => $compte?->type,
                'debut_blocage' => $compte?->debut_blocage
            ]);
            return;
        }

        $service->archiverCompte($compte);
    }
}
