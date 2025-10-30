<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Client;

class ClientSeeder extends Seeder
{
    public function run(): void
    {
        // Crée 10 clients liés à un user
        \App\Models\User::factory()->count(10)->create()->each(function ($user) {
            \App\Models\Client::factory()->create([
                'user_id' => $user->id,
            ]);
        });
    }
}
