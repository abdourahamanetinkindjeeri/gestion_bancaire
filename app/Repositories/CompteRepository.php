<?php

namespace App\Repositories;

use App\Models\Compte;
use Illuminate\Support\Facades\DB;

class CompteRepository extends BaseRepository
{
    protected array $searchable = ['numero_compte', 'type', 'devise'];
    protected array $sortable = ['numero_compte', 'type', 'solde_initial', 'statut', 'created_at', 'updated_at'];

    public function __construct(Compte $model)
    {
        parent::__construct($model);
    }





}
