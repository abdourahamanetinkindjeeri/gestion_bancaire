<?php

namespace App\Http\Resources;

use App\Services\ClientService;
use Illuminate\Http\Resources\Json\ResourceCollection;

class CompteResourceCollection extends ResourceCollection
{
    protected ClientService $clientService;

    public function __construct($resource, ClientService $clientService)
    {
        $this->clientService = $clientService;
        parent::__construct($resource);
    }

    public function toArray($request)
    {
        return $this->collection->map(function ($compte) {
            return new CompteResource($compte, $this->clientService);
        });
    }
}
