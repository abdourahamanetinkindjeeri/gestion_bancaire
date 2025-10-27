<?php

namespace Database\Factories;

use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    public function definition(): array
    {
        return [
            'id' => (string) Str::uuid(),
            'numero' => 'T' . str_pad($this->faker->unique()->numberBetween(1, 999999), 6, '0', STR_PAD_LEFT),
            'compte_id' => null, // toujours fourni explicitement dans le seeder
            'type' => $this->faker->randomElement(['depot', 'retrait', 'transfert']),
            'montant' => $this->faker->randomFloat(2, 100, 100000),
            'devise' => 'FCFA',
            'statut' => $this->faker->randomElement(['en_attente', 'complete', 'echouee']),
            'date_transaction' => $this->faker->date(),
            'metadata' => null,
        ];
    }
}
