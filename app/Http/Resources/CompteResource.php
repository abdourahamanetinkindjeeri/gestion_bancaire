<?php

namespace App\Http\Resources;

use App\Services\ClientService;
use Illuminate\Http\Resources\Json\JsonResource;

class CompteResource extends JsonResource
{
    protected ClientService $clientService;

    public function __construct($resource, ClientService $clientService)
    {
        // ⚠️ Toujours appeler le constructeur parent
        parent::__construct($resource);
        $this->clientService = $clientService;
    }

    public function toArray($request)
    {
        // On récupère le client associé
        $client = $this->clientService ? $this->clientService->getById($this->client_id) : null;

        // Si le client n'est pas trouvé localement, essayer de le récupérer depuis le cloud
        if (!$client && $this->clientService) {
            $client = $this->clientService->getByIdFromCloud($this->client_id);
        }

        return [
            'id'              => $this->id,
            'numero_compte'   => $this->numero_compte,
            'type'            => $this->type,
            'solde_initial'   => $this->solde_initial,
            'devise'          => $this->devise,
            'statut'          => $this->statut,
            'client_id'       => $this->client_id,
            'debut_blocage'   => $this->debut_blocage?->toISOString(),
            'fin_blocage'     => $this->fin_blocage?->toISOString(),
            'metadata'        => $this->metadata,
            'created_at'      => $this->created_at?->toISOString(),
            'updated_at'      => $this->updated_at?->toISOString(),
            'deleted_at'      => $this->deleted_at?->toISOString(),
        ];
    }
}
