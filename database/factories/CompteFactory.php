<?php

namespace Database\Factories;

use App\Models\Compte;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CompteFactory extends Factory
{
    protected $model = Compte::class;

    public function definition(): array
    {
        return [
            'id' => (string) Str::uuid(),
            'numero_compte' => 'CPT' . str_pad($this->faker->unique()->numberBetween(1, 99999999), 8, '0', STR_PAD_LEFT),
            'type' => $this->faker->randomElement(['cheque', 'epargne', 'courant']),
            'solde_initial' => $this->faker->randomFloat(2, 0, 1000000),
            'devise' => 'FCFA',
            'statut' => $this->faker->randomElement(['actif', 'suspendu', 'clôturé']),
            'client_id' => null, // toujours fourni explicitement dans le seeder
            'metadata' => null,
        ];
    }
}
