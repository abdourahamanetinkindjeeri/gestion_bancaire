<?php

namespace App\Jobs;

use App\Models\Compte;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ChangeCompteStatusJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $compteId;
    protected string $newStatus;

    public function __construct(string $compteId, string $newStatus)
    {
        $this->compteId = $compteId;
        $this->newStatus = $newStatus;
    }

    public function handle(): void
    {
        $compte = Compte::find($this->compteId);

        if (!$compte) {
            Log::warning("Compte introuvable pour le Job: {$this->compteId}");
            return;
        }

        $compte->update([
            'statut' => $this->newStatus,
        ]);

        Log::info("Statut du compte {$compte->numero_compte} changé à '{$this->newStatus}' via job.");
    }
}
