<?php

namespace App\Jobs;

use App\Models\Compte;
use App\Services\ArchiveCompteService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ArchiverCompteJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public string $compteId) {}

    public function handle(ArchiveCompteService $service): void
    {
        $compte = Compte::with('transactions')->find($this->compteId);

        if (! $compte || $compte->statut !== 'bloque') {
            return;
        }

        $service->archiverCompte($compte);
    }
}
