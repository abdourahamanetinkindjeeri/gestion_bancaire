<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Compte;
use App\Models\Client;

class CompteSeeder extends Seeder
{
    public function run(): void
    {
        Client::all()->each(function ($client) {
            $nb = rand(1, 3);
            Compte::factory()->count($nb)->create([
                'client_id' => $client->id, // UUID correctement passé
            ]);
        });
    }
}
