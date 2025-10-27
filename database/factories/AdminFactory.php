<?php

namespace Database\Factories;

use App\Models\Admin;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class AdminFactory extends Factory
{
    protected $model = Admin::class;

    public function definition(): array
    {
        return [
            'id' => $this->faker->uuid(),
            'nom' => $this->faker->lastName(),
            'prenom' => $this->faker->firstName(),
            'email' => $this->faker->unique()->safeEmail(),
            'telephone' => $this->faker->unique()->regexify('7[05678][0-9]{7}'),
            'password' => bcrypt('password'),
            'actif' => $this->faker->boolean(90),
            'metadata' => null,
        ];
    }
}
