<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Crée 5 users "simples" (ni admin ni client)
        User::factory()->count(5)->create();
    }
}
