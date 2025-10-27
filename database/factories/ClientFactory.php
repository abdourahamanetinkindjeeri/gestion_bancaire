<?php

namespace Database\Factories;

use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ClientFactory extends Factory
{
    protected $model = Client::class;

    public function definition(): array
    {
        return [
            'id' => (string) Str::uuid(),
            'code_client' => 'CL' . str_pad($this->faker->unique()->numberBetween(1, 99999999), 8, '0', STR_PAD_LEFT),
            'titulaire' => $this->faker->name(),
            'nci' => $this->faker->unique()->regexify('[A-Z0-9]{14}'),
            'email' => $this->faker->unique()->safeEmail(),
            'telephone' => $this->faker->unique()->regexify('7[05678][0-9]{7}'),
            'adresse' => $this->faker->address(),
            'actif' => $this->faker->boolean(90),
            'metadata' => null,
        ];
    }
}
