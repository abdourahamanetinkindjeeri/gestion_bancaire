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
            'numeroCompte'    => $this->numero_compte,
            'titulaire'       => $client ? $client->titulaire . ' ' . $client->prenom : null,
            'type'            => $this->type,
            'solde'           => $this->solde,
            'devise'          => $this->devise,
            'dateCreation'    => $this->created_at?->toISOString(),
            'statut'          => $this->statut,
            'motifBlocage'    => $this->motif_blocage,
            'metadata'        => [
                'derniereModification' => $this->updated_at?->toISOString(),
                'version'              => $this->version ?? 1,
            ],
        ];
    }
}
