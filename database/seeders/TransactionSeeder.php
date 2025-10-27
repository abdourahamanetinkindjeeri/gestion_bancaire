<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Transaction;
use App\Models\Compte;

class TransactionSeeder extends Seeder
{
    public function run(): void
    {
        Compte::all()->each(function ($compte) {
            Transaction::factory()->count(rand(5, 10))->create([
                'compte_id' => $compte->id, // UUID correctement passé
            ]);
        });
    }
}
