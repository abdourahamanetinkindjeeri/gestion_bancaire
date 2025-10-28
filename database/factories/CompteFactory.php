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
        $isBloque = $this->faker->boolean(30); // 30% de comptes bloqués

        $debutBlocage = $isBloque
            ? $this->faker->dateTimeBetween('0 days', 'now')->format('Y-m-d')
            : null;

        $finBlocage = $isBloque
            ? $this->faker->dateTimeBetween($debutBlocage, '+3 days')->format('Y-m-d')
            : null;

        return [
            'id' => (string) Str::uuid(),
            'numero_compte' => 'CPT' . str_pad($this->faker->unique()->numberBetween(1, 99999999), 8, '0', STR_PAD_LEFT),
            'type' => $this->faker->randomElement(['cheque', 'epargne', 'courant']),
            'solde_initial' => $this->faker->randomFloat(2, 0, 1000000),
            'devise' => 'FCFA',
            'statut' => $isBloque ? 'bloque' : $this->faker->randomElement(['actif', 'ferme']),
            'debut_blocage' => $debutBlocage,
            'fin_blocage' => $finBlocage,
            'client_id' => null, // fourni explicitement dans le seeder
            'metadata' => null,
        ];
    }

    /**
     * État spécifique pour générer des comptes bloqués manuellement.
     */
    public function bloque(): static
    {
        $debut = now()->subDays(rand(0, 2))->format('Y-m-d');
        $fin = now()->addDays(rand(1, 3))->format('Y-m-d');

        return $this->state(fn() => [
            'statut' => 'bloque',
            'debut_blocage' => $debut,
            'fin_blocage' => $fin,
        ]);
    }
}
